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
     * @var Slot[]
     */
    private $slots;

    /**
     * Constructor
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
     * @return Slot[]
     */
    public function getSlots()
    {
        return $this->slots;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return array(
            'id' => $this->getId(),
            'game' => $this->getGame()->jsonSerialize(),
            'slots' => $this->getSlots(),
        );
    }
}
