<?php

namespace Eole\Core\Event;

use Eole\Core\Model\Party;
use Eole\Core\Model\Player;
use Eole\Core\Model\Slot;

class SlotEvent extends Event
{
    /**
     * @var string
     */
    const JOIN_BEFORE = 'eole.core.event.slot.join_before';

    /**
     * @var string
     */
    const JOIN_AFTER = 'eole.core.event.slot.join_after';

    /**
     * @var Party
     */
    private $party;

    /**
     * @var Player
     */
    private $player;

    /**
     * @var Slot
     */
    private $slot;

    /**
     * @param Party $party
     * @param Player $player
     * @param Slot $slot
     */
    public function __construct(Party $party, Player $player = null, Slot $slot = null)
    {
        $this->party = $party;
        $this->player = $player;
        $this->slot = $slot;
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
    public function setPlayer(Player $player)
    {
        $this->player = $player;

        return $this;
    }

    /**
     * @return Slot
     */
    public function getSlot()
    {
        return $this->slot;
    }

    /**
     * @param Slot $slot
     *
     * @return self
     */
    public function setSlot(Slot $slot)
    {
        $this->slot = $slot;

        return $this;
    }

    /**
     * Create a SlotEvent instance from a slot.
     *
     * @param Slot $slot
     *
     * @return self
     */
    public static function createFromSlot(Slot $slot)
    {
        return new SlotEvent($slot->getParty(), $slot->getPlayer(), $slot);
    }
}
