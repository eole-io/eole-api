<?php

namespace Eole\Core\Event;

use Eole\Core\Model\Party;

class PartyEvent extends Event
{
    /**
     * @var string
     */
    const CREATE = 'eole.core.event.party.create';

    /**
     * @var Party
     */
    private $party;

    /**
     * @param Party $party
     */
    public function __construct(Party $party)
    {
        $this->party = $party;
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
}
