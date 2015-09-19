<?php

namespace Eole\Silex\Tests;

use Symfony\Component\Security\Core\User\UserInterface;
use Alcalyn\Wsse\Security\Authentication\Token\WsseUserToken;
use Alcalyn\Wsse\Security\Authentication\Provider\WsseTokenValidatorInterface;

class WsseTokenValidatorMock implements WsseTokenValidatorInterface
{
    /**
     * {@InheritDoc}
     */
    public function validateDigest(WsseUserToken $wsseToken, UserInterface $user)
    {
        return 'good-password' === $wsseToken->digest;
    }
}
