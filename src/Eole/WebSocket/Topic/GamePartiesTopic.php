<?php

namespace Eole\WebSocket\Topic;

use Ratchet\Wamp\WampConnection;
use Eole\Core\Model\Party;
use Eole\WebSocket\Topic;

class GamePartiesTopic extends Topic
{
    public function onSubscribe(WampConnection $conn, $topic)
    {
        parent::onSubscribe($conn, $topic);
    }

    public function onPublish(WampConnection $conn, $topic, $event)
    {
        // noop
    }

    /**
     * @param Party $party
     */
    public function onPartyCreated(Party $party)
    {
        $this->broadcast([
            'type' => 'created',
            'party' => $this->normalize($party),
        ]);
    }

    /**
     * @param Party $party
     */
    public function onPartyGone(Party $party)
    {
        $this->broadcast([
            'type' => 'gone',
            'party' => $this->normalize($party),
        ]);
    }

    public function onUnSubscribe(WampConnection $conn, $topic)
    {
        parent::onUnSubscribe($conn, $topic);
    }
}
