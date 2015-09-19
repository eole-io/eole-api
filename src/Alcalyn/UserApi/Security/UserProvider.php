<?php

namespace Alcalyn\UserApi\Security;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Alcalyn\UserApi\Api\ApiInterface;
use Alcalyn\UserApi\Model\User;

class UserProvider implements UserProviderInterface
{
    /**
     * @var ApiInterface
     */
    private $api;

    /**
     * @param ApiInterface $api
     */
    public function __construct(ApiInterface $api)
    {
        $this->api = $api;
    }

    /**
     * {@InheritDoc}
     */
    public function loadUserByUsername($username)
    {
        return $this->api->getUser($username);
    }

    /**
     * {@InheritDoc}
     */
    public function refreshUser(UserInterface $user)
    {
        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * {@InheritDoc}
     */
    public function supportsClass($class)
    {
        $userClass = User::class;

        return $userClass === $class || is_subclass_of($class, $userClass);
    }
}
