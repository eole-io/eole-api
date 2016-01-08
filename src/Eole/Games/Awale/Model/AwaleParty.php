<?php

namespace Eole\Games\Awale\Model;

use Alcalyn\Awale\Awale;
use Eole\Core\Model\Party;

class AwaleParty extends Awale
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Party
     */
    private $party;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
