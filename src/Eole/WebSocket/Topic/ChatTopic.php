<?php

namespace Eole\WebSocket\Topic;

use Ratchet\Wamp\WampConnection;
use Eole\WebSocket\Topic;

class ChatTopic extends Topic
{
    public function onSubscribe(WampConnection $conn, $topic)
    {
        parent::onSubscribe($conn, $topic);

        $this->broadcast([
            'type' => 'join',
            'player' => $this->normalize($conn->player),
        ]);
    }

    public function onPublish(WampConnection $conn, $topic, $event)
    {
        $this->broadcast([
            'type' => 'message',
            'player' => $this->normalize($conn->player),
            'message' => $event,
        ]);
    }

    public function onUnSubscribe(WampConnection $conn, $topic)
    {
        $this->broadcast([
            'type' => 'leave',
            'player' => $this->normalize($conn->player),
        ]);

        parent::onUnSubscribe($conn, $topic);
    }
}
