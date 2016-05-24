<?php

namespace Eole\WebSocket\Topic;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Ratchet\Wamp\WampConnection;
use Eole\Core\Model\Party;
use Eole\Core\Event\PartyEvent;
use Eole\Core\Event\SlotEvent;
use Eole\WebSocket\Topic;

class PartiesTopic extends Topic implements EventSubscriberInterface
{
    /**
     * @var null|string
     */
    private $gameName;

    /**
     * @param string $topicPath
     * @param array $arguments
     */
    public function __construct($topicPath, array $arguments = array())
    {
        parent::__construct($topicPath, $arguments);

        $this->gameName = $arguments['game_name'];
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
        // noop
    }

    /**
     * @param PartyEvent $event
     */
    public function onPartyAvailable(PartyEvent $event)
    {
        $party = $event->getParty();

        if (!$this->souldBroadcastForGame($party)) {
            return;
        }

        $this->broadcast([
            'type' => 'created',
            'party' => $party,
        ]);
    }

    /**
     * @param PartyEvent $event
     */
    public function onPartyGone(PartyEvent $event)
    {
        $party = $event->getParty();

        if (!$this->souldBroadcastForGame($party)) {
            return;
        }

        $this->broadcast([
            'type' => 'gone',
            'party' => $party,
        ]);
    }

    /**
     * @param SlotEvent $event
     */
    public function onSlotJoin(SlotEvent $event)
    {
        $party = $event->getParty();

        if (!$this->souldBroadcastForGame($party)) {
            return;
        }

        $this->broadcast([
            'type' => 'slot_join',
            'event' => $event,
        ]);
    }

    /**
     * @param PartyEvent $event
     */
    public function onPartyStarted(PartyEvent $event)
    {
        $party = $event->getParty();

        if (!$this->souldBroadcastForGame($party)) {
            return;
        }

        $this->broadcast([
            'type' => 'party_started',
            'event' => $event,
        ]);
    }

    /**
     * @param PartyEvent $event
     */
    public function onPartyEnded(PartyEvent $event)
    {
        $party = $event->getParty();

        if (!$this->souldBroadcastForGame($party)) {
            return;
        }

        $this->broadcast([
            'type' => 'party_ended',
            'event' => $event,
        ]);
    }

    /**
     * {@InheritDoc}
     */
    public function onUnSubscribe(WampConnection $conn, $topic)
    {
        parent::onUnSubscribe($conn, $topic);
    }

    /**
     * Returns whether it should broadcast event
     * depending on the current game name.
     *
     * @param Party $party
     *
     * @return bool
     */
    private function souldBroadcastForGame(Party $party)
    {
        return
            (null === $this->gameName) ||
            ($party->getGame()->getName() === $this->gameName)
        ;
    }

    /**
     * {@InheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            PartyEvent::CREATE_AFTER => array(
                array('onPartyAvailable'),
            ),
            SlotEvent::JOIN_AFTER => array(
                array('onSlotJoin'),
            ),
            PartyEvent::STARTED => array(
                array('onPartyStarted'),
            ),
            PartyEvent::ENDED => array(
                array('onPartyEnded'),
            ),
        );
    }
}
