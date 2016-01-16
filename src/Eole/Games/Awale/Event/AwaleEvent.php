<?php

namespace Eole\Games\Awale\Event;

use Eole\Core\Event\Event;
use Eole\Games\Awale\Model\AwaleParty;

class AwaleEvent extends Event
{
    /**
     * @var string
     */
    const PLAY = 'eole.games.awale.play';

    /**
     * @var string
     */
    const PARTY_END = 'eole.games.awale.party_end';

    /**
     * @var AwaleParty
     */
    private $awaleParty;

    /**
     * @var int|null
     */
    private $winner;

    /**
     * @param AwaleParty $awaleParty
     * @param int|null $winner
     */
    public function __construct(AwaleParty $awaleParty, $winner = null)
    {
        $this->awaleParty = $awaleParty;
        $this->winner = $winner;
    }

    /**
     * @return AwaleParty
     */
    public function getAwaleParty()
    {
        return $this->awaleParty;
    }

    /**
     * @return int|null
     */
    public function getWinner()
    {
        return $this->winner;
    }
}
