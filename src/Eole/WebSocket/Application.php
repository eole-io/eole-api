<?php

namespace Eole\WebSocket;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;
use Eole\Silex\Application as SilexApplication;
use Eole\WebSocket\Routing\TopicRoute;

class Application implements WampServerInterface
{
    /**
     * @var SilexApplication
     */
    private $silexApp;

    /**
     * @var Topic[]
     */
    private $topics;

    /**
     * @param SilexApplication $silexApp
     */
    public function __construct(SilexApplication $silexApp)
    {
        $this->silexApp = $silexApp;
        $this->topics = array();

        $this->registerServices();
        $this->registerTopics();
        $this->loadAllGames();
    }

    /**
     * Register Eole Websocket services.
     */
    private function registerServices()
    {
        $this->silexApp['eole.websocket_topic.normalizer'] = function () {
            return new Service\Normalizer(
                $this->silexApp['serializer'],
                $this->silexApp['serializer.context_factory']
            );
        };

        $this->silexApp->register(new ServiceProvider\TopicRoutingProvider());

        $this->silexApp['eole.websocket_topic.chat'] = function () {
            return new Topic\ChatTopic('eole/core/chat');
        };

        $gamePartiesFactory = function ($topicPath, array $arguments) {
            return new Topic\PartiesTopic($topicPath, $arguments);
        };

        $this->silexApp['eole.websocket_topic.game_parties.factory'] = $this->silexApp->protect($gamePartiesFactory);

        $this->silexApp['eole.websocket_topic.game_parties'] = function () use ($gamePartiesFactory) {
            return $gamePartiesFactory('eole/core/parties', array('game_name' => null));
        };
    }

    /**
     * Register base application topics.
     */
    private function registerTopics()
    {
        $this->silexApp['eole.websocket.routes']->add('eole_core_chat', new TopicRoute(
            $this->silexApp['eole.websocket_topic.chat']->getId(),
            $this->silexApp['eole.websocket_topic.chat']
        ));

        $this->silexApp['eole.websocket.routes']->add('eole_core_parties', new TopicRoute(
            $this->silexApp['eole.websocket_topic.game_parties']->getId(),
            $this->silexApp['eole.websocket_topic.game_parties']
        ));

        $this->silexApp['eole.websocket.routes']->add('eole_core_game_parties', new TopicRoute(
            'eole/core/game/{game_name}/parties',
            'eole.websocket_topic.game_parties.factory',
            array(),
            array('game_name' => '^[a-z0-9_\-]+$')
        ));
    }

    /**
     * @param string $gameName
     *
     * @return self
     *
     * @throws \LogicException
     */
    private function registerGame($gameName)
    {
        $gameConfig = $this->silexApp['environment']['games'][$gameName];

        if (isset($gameConfig['websocket_provider'])) {
            $serviceProviderClass = $gameConfig['websocket_provider'];
            $serviceProvider = new $serviceProviderClass();

            if (!$serviceProvider instanceof \Pimple\ServiceProviderInterface) {
                throw new \LogicException(sprintf(
                    'Websocket provider class (%s) for game %s must implement %s.',
                    $serviceProviderClass,
                    $gameName,
                    \Pimple\ServiceProviderInterface::class
                ));
            }

            $this->silexApp->register($serviceProvider);
        }

        return $this;
    }

    /**
     * @param string $gameName
     *
     * @return self
     */
    public function loadGame($gameName)
    {
        $this->registerGame($gameName);

        return $this;
    }

    /**
     * @return self
     */
    public function loadAllGames()
    {
        $games = $this->silexApp['environment']['games'];

        foreach ($games as $gameName => $config) {
            $this->loadGame($gameName);
        }

        return $this;
    }

    /**
     * @param ConnectionInterface $conn
     *
     * @return \Eole\Core\Model\Player
     *
     * @throws \Exception
     */
    private function authenticatePlayer(ConnectionInterface $conn)
    {
        $accessToken = $conn->WebSocket->request->getQuery()->get('access_token');

        if (null === $accessToken) {
            throw new \Exception('Missing OAuth token in query.');
        }

        $resourceServer = $this->silexApp['eole.oauth.resource_server'];
        $userProvider = $this->silexApp['eole.user_provider'];

        $resourceServer->isValidRequest(true, $accessToken);
        $username = $resourceServer->getAccessToken()->getSession()->getId();
        $user = $userProvider->loadUserByUsername($username);
        $isUser = $user instanceof \Symfony\Component\Security\Core\User\UserInterface;

        if (!$isUser) {
            throw new \Exception('User not found.');
        }

        return $user;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        echo __METHOD__.' authentication... ';

        try {
            $player = $this->authenticatePlayer($conn);
            echo sprintf('Player "%s" logged.'.PHP_EOL, $player->getUsername());
        } catch (\Exception $e) {
            echo 'failed: '.$e->getMessage().PHP_EOL;
            $conn->send(json_encode('Could not authenticate client, closing connection.'));
            $conn->close();

            return;
        }

        $conn->player = $player;
    }

    private function getTopic($topicPath)
    {
        if (!isset($this->topics[$topicPath])) {
            $this->topics[$topicPath] = $this->loadTopic($topicPath);
        }

        return $this->topics[$topicPath];
    }

    /**
     * @param string $topicPath
     *
     * @return Topic
     */
    private function loadTopic($topicPath)
    {
        $topic = $this->silexApp['eole.websocket.router']->loadTopic($topicPath);

        $topic->setNormalizer($this->silexApp['eole.websocket_topic.normalizer']);

        if ($topic instanceof EventSubscriberInterface) {
            $this->silexApp['dispatcher']->addSubscriber($topic);
        }

        return $topic;
    }

    public function onSubscribe(ConnectionInterface $conn, $topic)
    {
        echo __METHOD__.' '.$topic.PHP_EOL;

        $this->getTopic($topic)->onSubscribe($conn, $topic);
    }

    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible)
    {
        echo __METHOD__.' '.$topic.PHP_EOL;

        $this->topics[$topic]->onPublish($conn, $topic, $event, $exclude, $eligible);
    }

    public function onUnSubscribe(ConnectionInterface $conn, $topic)
    {
        echo __METHOD__.' '.$topic.PHP_EOL;
        $this->topics[$topic]->onUnSubscribe($conn, $topic);
    }

    public function onClose(ConnectionInterface $conn)
    {
        echo __METHOD__.PHP_EOL;
    }

    public function onCall(ConnectionInterface $conn, $id, $topic, array $params)
    {
        echo __METHOD__.PHP_EOL;
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo __METHOD__.' '.$e->getMessage().PHP_EOL;
    }
}
