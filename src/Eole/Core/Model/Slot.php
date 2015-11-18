<?php

namespace Eole\Core\Model;

class Slot implements \JsonSerializable
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
     * @param Player $player
     *
     * @return self
     */
    public function setPlayer(Player $player = null)
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
     * @return array
     */
    public function jsonSerialize()
    {
        return array(
            'id'        => $this->getId(),
            'player'    => is_null($this->getPlayer()) ? null : $this->getPlayer()->jsonSerialize(),
        );
    }
}
