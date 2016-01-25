<?php

namespace Eole\OAuth2;

use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\AuthorizationServer as BaseAuthorizationServer;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Eole\OAuth2\Storage\Client;
use Eole\OAuth2\Storage\Session;
use Eole\OAuth2\Storage\AccessToken;
use Eole\OAuth2\Storage\Scope;

class AuthorizationServer extends BaseAuthorizationServer
{
    /**
     * @var string
     */
    private $tokensDir;

    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * @var EncoderFactoryInterface
     */
    private $encoderFactory;

    /**
     * @param string $tokensDir
     * @param UserProviderInterface $userProvider
     * @param EncoderFactoryInterface $encoderFactory
     */
    public function __construct(
        $tokensDir,
        UserProviderInterface $userProvider,
        EncoderFactoryInterface $encoderFactory
    ) {
        parent::__construct();

        $this->tokensDir = $tokensDir;
        $this->userProvider = $userProvider;
        $this->encoderFactory = $encoderFactory;

        $this->init();
    }

    /**
     * @return self
     */
    private function init()
    {
        if (!is_dir($this->tokensDir)) {
            mkdir($this->tokensDir, 0777, true);
        }

        $this
            ->setClientStorage(new Client())
            ->setSessionStorage(new Session())
            ->setAccessTokenStorage(new AccessToken($this->tokensDir))
            ->setScopeStorage(new Scope())
        ;

        $passwordGrant = new PasswordGrant();

        $passwordGrant->setVerifyCredentialsCallback(function ($username, $password) {
            $user = $this->userProvider->loadUserByUsername($username);
            $encoder = $this->encoderFactory->getEncoder($user);
            $isPasswordValid = $encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt());

            if ($isPasswordValid) {
                return $username;
            } else {
                return false;
            }
        });

        $this->addGrantType($passwordGrant);

        return $this;
    }
}
