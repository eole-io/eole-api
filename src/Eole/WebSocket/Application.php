<?php

namespace Eole\WebSocket;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Alcalyn\Wsse\Security\Authentication\Token\WsseUserToken;
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
        $this->registerGames();
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
     * Register all games.
     */
    private function registerGames()
    {
        $this->silexApp->register(new \Eole\Games\TicTacToe\TicTacToeProvider());
    }

    /**
     * @param ConnectionInterface $conn
     *
     * @return \Eole\Core\Model\Player
     *
     * @throws \Symfony\Component\Security\Core\Exception\AuthenticationException
     * @throws \Exception
     */
    private function authenticatePlayer(ConnectionInterface $conn)
    {
        $wsseTokenRaw = $conn->WebSocket->request->getQuery()->get('wsse_token');

        if (null === $wsseTokenRaw) {
            throw new \Exception('Missing Wsse token in query.');
        }

        $tokenValidator = $this->silexApp['security.wsse.token_validator'];
        $userProvider = $this->silexApp['eole.user_provider'];

        $wsseTokenObject = json_decode(base64_decode($wsseTokenRaw));

        $wsseToken = new WsseUserToken();
        $wsseToken->created = $wsseTokenObject->created;
        $wsseToken->digest = $wsseTokenObject->digest;
        $wsseToken->nonce = $wsseTokenObject->nonce;

        $player = $userProvider->loadUserByUsername($wsseTokenObject->username);

        if (null === $player) {
            throw new \Exception(sprintf('Could not retrieve player "%s".', $wsseTokenObject->username));
        }

        $tokenValidator->validateDigest($wsseToken, $player);

        return $player;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        echo __METHOD__.PHP_EOL;

        try {
            $player = $this->authenticatePlayer($conn);
        } catch (\Exception $e) {
            echo $e->getMessage().PHP_EOL;
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
