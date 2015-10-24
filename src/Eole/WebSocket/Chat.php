<?php

namespace Eole\WebSocket;

use Ratchet\Wamp\WampConnection;

class Chat extends ApplicationTopic
{
    public function onPublish(WampConnection $conn, $topic, $event)
    {
        parent::onPublish($conn, $topic, $event);

        $this->broadcast($event);
    }
}
