<?php

namespace Eole\Core\Service;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Eole\Core\Model\Game;
use Eole\Core\Model\Player;
use Eole\Core\Model\Party;
use Eole\Core\Event\PartyEvent;

class PartyManager
{
    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    /**
     * @param EventDispatcher $dispatcher
     */
    public function __construct(EventDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

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
        ;

        $this->dispatcher->dispatch(PartyEvent::CREATE, new PartyEvent($party));

        return $party;
    }
}
