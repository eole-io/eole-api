<?php

namespace Eole\Core\Model;

use Eole\Core\Model\Player;
use Eole\Core\Model\Party;

class Slot
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Player
     */
    private $player;

    /**
     * @var Party
     */
    private $party;

    /**
     * @var float
     */
    private $score;

    /**
     * @param Party $party
     * @param Player $player
     */
    public function __construct(Party $party, Player $player = null)
    {
        $this->party = $party;
        $this->player = $player;
        $this->score = 0;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Player
     */
    public function getPlayer()
    {
        return $this->player;
    }

    /**
     * @return bool
     */
    public function hasPlayer()
    {
        return null !== $this->player;
    }

    /**
     * @return bool
     */
    public function isFree()
    {
        return null === $this->player;
    }

    /**
     * @param Player $player
     *
     * @return self
     */
    public function setPlayer(Player $player)
    {
        $this->player = $player;

        return $this;
    }

    /**
     * @return Party
     */
    public function getParty()
    {
        return $this->party;
    }

    /**
     * @param Party $party
     *
     * @return self
     */
    public function setParty(Party $party)
    {
        $this->party = $party;

        return $this;
    }

    /**
     * @return float
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * @param float $score
     *
     * @return self
     */
    public function setScore($score)
    {
        $this->score = $score;

        return $this;
    }

    /**
     * @param float $n
     *
     * @return self
     */
    public function incrementScore($n = 1)
    {
        $this->score += $n;

        return $this;
    }
}
