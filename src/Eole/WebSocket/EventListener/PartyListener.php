<?php

namespace Eole\WebSocket\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Eole\Core\Event\PartyEvent;
use Eole\WebSocket\ApplicationTopic\GamePartiesTopic;

class PartyListener implements EventSubscriberInterface
{
    /**
     * @var GamePartiesTopic
     */
    private $gamePartiesTopic;

    /**
     * @param GamePartiesTopic $gamePartiesTopic
     */
    public function __construct(GamePartiesTopic $gamePartiesTopic)
    {
        $this->gamePartiesTopic = $gamePartiesTopic;
    }

    /**
     * {@InheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            PartyEvent::CREATE => array(
                array('onPartyCreate'),
            ),
        );
    }

    /**
     * @param PartyEvent $event
     */
    public function onPartyCreate(PartyEvent $event)
    {
        $this->gamePartiesTopic->onPartyCreated($event->getParty());
    }
}
