<?php

namespace Eole\Core\Model;

use Alcalyn\UserApi\Model\User;

class Player extends User
{
    /**
     * @var bool
     */
    private $guest;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this
            ->setGuest(false)
            ->setEnabled(true)
            ->setEmailVerified(false)
        ;
    }

    /**
     * @return bool
     */
    public function getGuest()
    {
        return $this->guest;
    }

    /**
     * @return bool
     */
    public function isGuest()
    {
        return $this->guest;
    }

    /**
     * @param bool $guest
     *
     * @return Player
     */
    public function setGuest($guest)
    {
        $this->guest = $guest;

        return $this;
    }

    /**
     * {@InheritDoc}
     */
    public function getRoles()
    {
        return array('ROLE_PLAYER');
    }
}
