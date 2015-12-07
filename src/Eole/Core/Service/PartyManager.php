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
}
