<?php

namespace Eole\Games\TicTacToe;

use Ratchet\Wamp\WampConnection;
use Eole\Core\Model\Party;
use Eole\WebSocket\Topic as BaseTopic;

class Topic extends BaseTopic
{
    /**
     * @var Party
     */
    private $party;

    /**
     * @param string $topicPath
     * @param Party $party
     */
    public function __construct($topicPath, Party $party)
    {
        parent::__construct($topicPath);

        $this->party = $party;
    }

    /**
     * {@InheritDoc}
     */
    public function onSubscribe(WampConnection $conn, $topic)
    {
        parent::onSubscribe($conn, $topic);
    }

    /**
     * {@InheritDoc}
     */
    public function onPublish(WampConnection $conn, $topic, $event)
    {
    }

    /**
     * {@InheritDoc}
     */
    public function onUnSubscribe(WampConnection $conn, $topic)
    {
        parent::onUnSubscribe($conn, $topic);
    }
}
