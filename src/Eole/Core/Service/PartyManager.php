<?php

namespace Eole\Core\Service;

use Eole\Core\Model\Game;
use Eole\Core\Model\Player;
use Eole\Core\Model\Party;
use Eole\Core\Model\Slot;

class PartyManager
{
    /**
     * @param Game $game
     * @param Player $host
     *
     * @return Party
     */
    public function createParty(Game $game, Player $host)
    {
        $party = new Party();

        $party
            ->setGame($game)
            ->setHost($host)
            ->addSlot(new Slot($party, $host))
            ->addSlot(new Slot($party))
        ;

        return $party;
    }

    /**
     * @param Party $party
     * @param Player $player
     *
     * @return int|null player position or null if player is not in party.
     */
    public function getPlayerPosition(Party $party, Player $player)
    {
        if (null === $player->getId()) {
            throw new \RuntimeException('Player id is null.');
        }

        foreach ($party->getSlots() as $position => $slot) {
            if ($slot->hasPlayer() && ($slot->getPlayer()->getId() === $player->getId())) {
                return $position;
            }
        }

        return null;
    }

    /**
     * Check if player is in party.
     *
     * @param Party $party
     * @param Player $player
     *
     * @return bool
     */
    public function hasPlayer(Party $party, Player $player)
    {
        return null !== $this->getPlayerPosition($party, $player);
    }

    /**
     * @param Party $party
     *
     * @return int
     */
    public function getFreeSlotsCount(Party $party)
    {
        $freeSlots = 0;

        foreach ($party->getSlots() as $slot) {
            if ($slot->isFree()) {
                $freeSlots++;
            }
        }

        return $freeSlots;
    }

    /**
     * @param Party $party
     *
     * @return boolean
     */
    public function hasFreeSlot(Party $party)
    {
        $slots = $party->getSlots();
        $slotsCount = count($slots);

        for ($i = $slotsCount - 1; $i >= 0; $i--) {
            if ($slots[$i]->isFree()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Party $party
     * @param Player $player
     *
     * @return int position of new player.
     *
     * @throws \OverflowException
     */
    public function addPlayer(Party $party, Player $player)
    {
        foreach ($party->getSlots() as $position => $slot) {
            if ($slot->isFree()) {
                $slot->setPlayer($player);

                return $position;
            }
        }

        throw new \OverflowException('Party is full, cannot add player.');
    }

    /**
     * @param Party $party
     *
     * @throws \RuntimeException
     */
    public function startParty(Party $party)
    {
        if (Party::PREPARATION !== $party->getState()) {
            throw new \RuntimeException('Party already started');
        }

        $party->setState(Party::ACTIVE);
    }

    /**
     * @param Party $party
     *
     * @throws \RuntimeException
     */
    public function endParty(Party $party)
    {
        if (Party::ACTIVE !== $party->getState()) {
            throw new \RuntimeException('Party is not active');
        }

        $party->setState(Party::ENDED);
    }
}
