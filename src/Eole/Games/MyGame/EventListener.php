<?php

namespace Eole\Games\Awale;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Eole\Core\Event\PartyEvent;
use Eole\Core\Service\PartyManager;

class EventListener implements EventSubscriberInterface
{
    /**
     * @var PartyManager
     */
    private $partyManager;

    /**
     * @param PartyManager $partyManager
     */
    public function __construct(PartyManager $partyManager)
    {
        $this->partyManager = $partyManager;
    }

    /**
     * @param PartyEvent $event
     */
    public function onPartyCreateBefore(PartyEvent $event)
    {
        $party = $event->getParty();

        if (MyGame::GAME_NAME !== $party->getGame()->getName()) {
            return;
        }

        $party
            ->addEmptySlot()
            ->addEmptySlot()
            ->addEmptySlot()
            ->addEmptySlot()
            ->addEmptySlot()
            ->addEmptySlot()
        ;

        $this->partyManager->reorderSlots($party);
    }

    /**
     * {@InheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            PartyEvent::CREATE_BEFORE => 'onPartyCreateBefore',
        ];
    }
}
