<?php

namespace Eole\WebSocket\Topic;

use Ratchet\Wamp\WampConnection;
use Eole\Sandstone\Websocket\Topic;

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
            'player' => $conn->player,
        ]);
    }

    /**
     * {@InheritDoc}
     */
    public function onPublish(WampConnection $conn, $topic, $event)
    {
        $this->broadcast([
            'type' => 'message',
            'player' => $conn->player,
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
            'player' => $conn->player,
        ]);

        parent::onUnSubscribe($conn, $topic);
    }
}
