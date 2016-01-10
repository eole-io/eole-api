<?php

namespace Eole\Games\Awale\Event;

use Eole\Core\Event\Event;
use Eole\Games\Awale\Model\AwaleParty;

class AwaleEvent extends Event
{
    /**
     * @var string
     */
    const PLAY_AFTER = 'eole.games.awale.play_after';

    /**
     * @var AwaleParty
     */
    private $awaleParty;

    /**
     * @param AwaleParty $awaleParty
     */
    public function __construct(AwaleParty $awaleParty)
    {
        $this->awaleParty = $awaleParty;
    }

    /**
     * @return AwaleParty
     */
    public function getAwaleParty()
    {
        return $this->awaleParty;
    }
}
