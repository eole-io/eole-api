<?php

namespace Eole\Core\Event;

use Eole\Core\Model\Party;

class PartyEvent extends Event
{
    /**
     * This event is dispatched when a Party is creating,
     * before flushing it to database.
     * It contains an instance of PartyEvent with a Party instance.
     *
     * @var string
     */
    const CREATE_BEFORE = 'eole.core.event.party.create_before';

    /**
     * This event is dispatched once a created party has been flushed
     * to database.
     *
     * @var string
     */
    const CREATE_AFTER = 'eole.core.event.party.create_after';

    /**
     * This event is dispatched when party stated is set to started
     * using the PartyManager service.
     * The party instance is not yet persisted.
     *
     * @var string
     */
    const STARTED = 'eole.core.event.party.started';

    /**
     * This event is dispatched when party state is set to end
     * using the PartyManager service.
     * The party instance is not yet persisted.
     *
     * @var string
     */
    const ENDED = 'eole.core.event.party.ended';

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
