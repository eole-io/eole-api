<?php

namespace Eole\Core\Model;

class Party implements \JsonSerializable
{
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
     * @var Slot[]
     */
    private $slots;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->slots = array();
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
     * @return Slot[]
     */
    public function getSlots()
    {
        return $this->slots;
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
