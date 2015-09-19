<?php

namespace Alcalyn\UserApi\Exception;

class UserNotFoundException extends \Exception
{
    /**
     * @param string $username
     */
    public function __construct($username = null)
    {
        parent::__construct('User '.$username.' not found.', 0, null);
    }
}
