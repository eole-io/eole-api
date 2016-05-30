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
        $this->loadAllMods();
    }

    /**
     * Register Eole Websocket services.
     */
    private function registerServices()
    {
        $this->silexApp->register(new ServiceProvider\TopicRoutingProvider());
    }

    /**
     * @param string $modName
     *
     * @return self
     *
     * @throws \LogicException
     */
    public function loadMod($modName)
    {
        $mod = $this->silexApp->instanciateMod($modName);
        $websocketProvider = $mod->createWebsocketProvider();

        if (null === $websocketProvider) {
            return $this;
        }

        if (!$websocketProvider instanceof \Pimple\ServiceProviderInterface) {
            throw new \LogicException(sprintf(
                'Websocket provider class (%s) for mod %s must implement %s.',
                get_class($websocketProvider),
                $modName,
                \Pimple\ServiceProviderInterface::class
            ));
        }

        $this->silexApp->register($websocketProvider);

        return $this;
    }

    /**
     * @return self
     */
    public function loadAllMods()
    {
        $mods = $this->silexApp['environment']['mods'];

        foreach ($mods as $modName => $config) {
            $this->loadMod($modName);
        }

        return $this;
    }

    /**
     * @param ConnectionInterface $conn
     *
     * @return \Symfony\Component\Security\Core\User\UserInterface
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

    /**
     * {@InheritDoc}
     */
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

    /**
     * {@InheritDoc}
     */
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

        $topic->setNormalizer($this->silexApp['serializer']);

        if ($topic instanceof EventSubscriberInterface) {
            $this->silexApp['dispatcher']->addSubscriber($topic);
        }

        return $topic;
    }

    /**
     * {@InheritDoc}
     */
    public function onSubscribe(ConnectionInterface $conn, $topic)
    {
        echo __METHOD__.' '.$topic.PHP_EOL;

        $this->getTopic($topic)->onSubscribe($conn, $topic);
    }

    /**
     * {@InheritDoc}
     */
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible)
    {
        echo __METHOD__.' '.$topic.PHP_EOL;

        $this->topics[$topic]->onPublish($conn, $topic, $event);
    }

    /**
     * {@InheritDoc}
     */
    public function onUnSubscribe(ConnectionInterface $conn, $topic)
    {
        echo __METHOD__.' '.$topic.PHP_EOL;

        $this->topics[$topic]->onUnSubscribe($conn, $topic);
    }

    /**
     * {@InheritDoc}
     */
    public function onClose(ConnectionInterface $conn)
    {
        echo __METHOD__.PHP_EOL;

        foreach ($this->topics as $topic) {
            if ($topic->has($conn)) {
                $topic->onUnSubscribe($conn, $topic);
            }
        }
    }

    /**
     * {@InheritDoc}
     */
    public function onCall(ConnectionInterface $conn, $id, $topic, array $params)
    {
        echo __METHOD__.PHP_EOL;
    }

    /**
     * {@InheritDoc}
     */
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo __METHOD__.' '.$e->getMessage().PHP_EOL;
    }
}
