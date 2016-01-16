<?php

namespace Eole\Games\Awale;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Eole\Core\Event\SlotEvent;
use Eole\WebSocket\Topic as BaseTopic;
use Eole\Games\Awale\Event\AwaleEvent;

class AwaleTopic extends BaseTopic implements EventSubscriberInterface
{
    /**
     * @var int
     */
    private $partyId;

    /**
     * @param string $topicPath
     * @param int $partyId
     */
    public function __construct($topicPath, $partyId)
    {
        parent::__construct($topicPath);

        $this->partyId = $partyId;
    }

    /**
     * @param AwaleEvent $event
     */
    public function onPlay(AwaleEvent $event)
    {
        $this->broadcast(array(
            'type' => 'played',
            'move' => $event->getAwaleParty()->getLastMove(),
            'current_player' => $event->getAwaleParty()->getCurrentPlayer(),
        ));
    }

    /**
     * @param SlotEvent $event
     */
    public function onPlayerJoin(SlotEvent $event)
    {
        if ($event->getParty()->getId() !== $this->partyId) {
            return;
        }

        $this->broadcast(array(
            'type' => 'join',
            'party' => $this->normalizer->normalize($event->getParty()),
        ));
    }

    /**
     * @param AwaleEvent $event
     */
    public function onPartyEnd(AwaleEvent $event)
    {
        $this->broadcast(array(
            'type' => 'party_end',
            'winner' => $event->getWinner(),
        ));
    }

    /**
     * {@InheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            SlotEvent::JOIN_AFTER => array(
                array('onPlayerJoin'),
            ),
            AwaleEvent::PLAY => array(
                array('onPlay'),
            ),
            AwaleEvent::PARTY_END => array(
                array('onPartyEnd'),
            ),
        );
    }
}
