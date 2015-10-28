<?php

namespace Eole\WebSocket;

use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;
use Eole\Silex\Application as SilexApplication;

class Application implements WampServerInterface
{
    /**
     * @var SilexApplication
     */
    private $silexApp;

    /**
     * @var ApplicationTopic[]
     */
    private $topics;

    /**
     * @param SilexApplication $silexApp
     */
    public function __construct(SilexApplication $silexApp)
    {
        $this->silexApp = $silexApp;
        $this->topics = array();

        foreach ($silexApp['websocket.topics'] as $topic) {
            $this->topics[$topic->getId()] = $topic;
        }
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $wsseToken = $conn->WebSocket->request->getQuery()->get('wsse_token');

        if (null === $wsseToken) {
            $reason = 'Closing because missing Wsse token.';
            $conn->send($reason);
            echo $reason.PHP_EOL;
            $conn->close();
        }

        $tokenValidator = $this->silexApp['security.wsse.token_validator'];
        $userProvider = $this->silexApp['eole.user_provider'];

        // Authenticate user from Wsse token, or close connection

        echo __METHOD__.PHP_EOL;
    }

    public function onSubscribe(ConnectionInterface $conn, $topic)
    {
        echo __METHOD__.PHP_EOL;
        $this->topics[$topic]->onSubscribe($conn, $topic);
    }

    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible)
    {
        echo __METHOD__.PHP_EOL;
        $this->topics[$topic]->onPublish($conn, $topic, $event, $exclude, $eligible);
    }

    public function onUnSubscribe(ConnectionInterface $conn, $topic)
    {
        echo __METHOD__.PHP_EOL;
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
        echo __METHOD__.PHP_EOL;
    }
}
