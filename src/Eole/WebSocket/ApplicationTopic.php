<?php

namespace Eole\WebSocket;

use Ratchet\Wamp\WampConnection;
use Ratchet\Wamp\Topic;

abstract class ApplicationTopic extends Topic
{
    public function onSubscribe(WampConnection $conn, $topic)
    {
        $this->add($conn);
    }

    abstract public function onPublish(WampConnection $conn, $topic, $event);

    public function onUnSubscribe(WampConnection $conn, $topic)
    {
        $this->remove($conn);
    }
}
