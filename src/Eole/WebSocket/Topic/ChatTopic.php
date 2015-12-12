<?php

namespace Eole\WebSocket\Topic;

use Ratchet\Wamp\WampConnection;
use Eole\WebSocket\Topic;

class ChatTopic extends Topic
{
    /**
     * {@InheritDoc}
     */
    public function onSubscribe(WampConnection $conn, $topic)
    {
        parent::onSubscribe($conn, $topic);

        $this->broadcast([
            'type' => 'join',
            'player' => $this->normalizer->normalize($conn->player),
        ]);
    }

    /**
     * {@InheritDoc}
     */
    public function onPublish(WampConnection $conn, $topic, $event)
    {
        $this->broadcast([
            'type' => 'message',
            'player' => $this->normalizer->normalize($conn->player),
            'message' => $event,
        ]);
    }

    /**
     * {@InheritDoc}
     */
    public function onUnSubscribe(WampConnection $conn, $topic)
    {
        $this->broadcast([
            'type' => 'leave',
            'player' => $this->normalizer->normalize($conn->player),
        ]);

        parent::onUnSubscribe($conn, $topic);
    }
}
