<?php

namespace Eole\Games\MyGame;

use Eole\Core\Event\PartyEvent;
use Eole\Core\Service\PartyManager;

class PartyListener
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
        $eoleParty = $event->getParty();

        // Only do something when the party created event comes from "my-game"
        if ('my-game' !== $eoleParty->getGame()->getName()) {
            return;
        }

        // Add an empty slot
        $eoleParty->addEmptySlot();
        $eoleParty->addEmptySlot();
        $eoleParty->addEmptySlot();
        $eoleParty->addEmptySlot();
        $eoleParty->addEmptySlot();
        $eoleParty->addEmptySlot();

        // Automatically set an order to slots (or the order of the new slot will be empty)
        $this->partyManager->reorderSlots($eoleParty);
    }
}
