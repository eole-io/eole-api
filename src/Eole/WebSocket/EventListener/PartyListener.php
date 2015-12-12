<?php

namespace Eole\WebSocket\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Eole\Core\Event\PartyEvent;
use Eole\WebSocket\Topic\PartiesTopic;

class PartyListener implements EventSubscriberInterface
{
    /**
     * @var PartiesTopic
     */
    private $partiesTopic;

    /**
     * @param PartiesTopic $partiesTopic
     */
    public function __construct(PartiesTopic $partiesTopic)
    {
        $this->partiesTopic = $partiesTopic;
    }

    /**
     * {@InheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            PartyEvent::CREATE_AFTER => array(
                array('onPartyCreate'),
            ),
        );
    }

    /**
     * @param PartyEvent $event
     */
    public function onPartyCreate(PartyEvent $event)
    {
        $this->partiesTopic->onPartyCreated($event->getParty());
    }
}
