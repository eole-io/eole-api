<?php

namespace Eole\Core\Model;

class Party implements \JsonSerializable
{
    /**
     * @var int
     */
    const PREPARATION = 0;

    /**
     * @var int
     */
    const ACTIVE = 1;

    /**
     * @var int
     */
    const ENDED = 2;

    /**
     * @var int
     */
    private $id;

    /**
     * @var Game
     */
    private $game;

    /**
     * @var Player
     */
    private $host;

    /**
     * @var int
     */
    private $state;

    /**
     * @var Slot[]
     */
    private $slots;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->slots = array();
        $this->state = Party::PREPARATION;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Game
     */
    public function getGame()
    {
        return $this->game;
    }

    /**
     * @param Game $game
     *
     * @return self
     */
    public function setGame(Game $game)
    {
        $this->game = $game;

        return $this;
    }

    /**
     * @return Player
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param Player $player
     *
     * @return self
     */
    public function setHost(Player $player)
    {
        $this->host = $player;

        return $this;
    }

    /**
     * @return int
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param int $state
     *
     * @return self
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return Slot[]
     */
    public function getSlots()
    {
        return $this->slots;
    }

    /**
     * @param int $position
     *
     * @return Slot
     */
    public function getSlot($position)
    {
        return $this->slots[$position];
    }

    /**
     * @param Slot[] $slots
     *
     * @return self
     */
    public function setSlots(array $slots)
    {
        $this->slots = $slots;

        return $this;
    }

    /**
     * @param Slot $slot
     *
     * @return self
     */
    public function addSlot(Slot $slot)
    {
        $this->slots []= $slot;

        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return array(
            'id' => $this->getId(),
            'game' => $this->getGame(),
            'host' => $this->getHost(),
            'slots' => $this->getSlots(),
        );
    }
}
