<?php

namespace Eole\Websocket\Topic;

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
            'player' => $conn->user,
        ]);
    }

    /**
     * {@InheritDoc}
     */
    public function onPublish(WampConnection $conn, $topic, $event)
    {
        $this->broadcast([
            'type' => 'message',
            'player' => $conn->user,
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
            'player' => $conn->user,
        ]);

        parent::onUnSubscribe($conn, $topic);
    }
}
