<?php

namespace Eole\WebSocket;

use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

class Application implements WampServerInterface
{
    /**
     * @var ApplicationTopicInterface[]
     */
    private $topics;

    public function addApplicationTopic($path, ApplicationTopicInterface $topic)
    {
        $this->topics[$path] = $topic;
    }

    public function onOpen(ConnectionInterface $conn)
    {
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
