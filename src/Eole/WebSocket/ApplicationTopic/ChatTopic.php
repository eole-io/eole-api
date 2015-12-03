<?php

namespace Eole\WebSocket\ApplicationTopic;

use Ratchet\Wamp\WampConnection;
use Eole\WebSocket\ApplicationTopic;

class ChatTopic extends ApplicationTopic
{
    public function onSubscribe(WampConnection $conn, $topic)
    {
        parent::onSubscribe($conn, $topic);

        $this->broadcast([
            'type' => 'join',
            'player' => $conn->player,
        ]);
    }

    public function onPublish(WampConnection $conn, $topic, $event)
    {
        $this->broadcast([
            'type' => 'message',
            'player' => $conn->player,
            'message' => $event,
        ]);
    }

    public function onUnSubscribe(WampConnection $conn, $topic)
    {
        $this->broadcast([
            'type' => 'leave',
            'player' => $conn->player,
        ]);

        parent::onUnSubscribe($conn, $topic);
    }
}
