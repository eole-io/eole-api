<?php

namespace Eole\OAuth2;

use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\AuthorizationServer as BaseAuthorizationServer;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Eole\OAuth2\Storage\Client;
use Eole\OAuth2\Storage\Session;
use Eole\OAuth2\Storage\AccessToken;
use Eole\OAuth2\Storage\Scope;
use Eole\OAuth2\Storage\RefreshToken;

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

        $this->touchTokensDir();
        $this->initServer();
        $this->addPasswordGrant();
        $this->addRefreshTokenGrant();
    }

    /**
     * Check tokens directory exists or create it.
     */
    private function touchTokensDir()
    {
        if (!is_dir($this->tokensDir.'/access-tokens')) {
            mkdir($this->tokensDir.'/access-tokens', 0777, true);
        }

        if (!is_dir($this->tokensDir.'/refresh-tokens')) {
            mkdir($this->tokensDir.'/refresh-tokens', 0777, true);
        }
    }

    /**
     * Init authorization server.
     */
    private function initServer()
    {
        $this
            ->setClientStorage(new Client())
            ->setSessionStorage(new Session($this->tokensDir.'/access-tokens'))
            ->setAccessTokenStorage(new AccessToken($this->tokensDir.'/access-tokens'))
            ->setScopeStorage(new Scope())
        ;

        return $this;
    }

    /**
     * Allows authorization server to deliver an access token with username/password.
     */
    private function addPasswordGrant()
    {
        $passwordGrant = new PasswordGrant();

        $passwordGrant->setVerifyCredentialsCallback(function ($username, $password) {
            $user = $this->userProvider->loadUserByUsername($username);

            if (null === $user) {
                return false;
            }

            $encoder = $this->encoderFactory->getEncoder($user);
            $isPasswordValid = $encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt());

            if ($isPasswordValid) {
                return $username;
            } else {
                return false;
            }
        });

        $this->addGrantType($passwordGrant);
    }

    /**
     * Allows authorization server to deliver a fresh access token with an old one.
     */
    private function addRefreshTokenGrant()
    {
        $this->setRefreshTokenStorage(new RefreshToken($this->tokensDir.'/refresh-tokens'));

        $refreshTokenGrant = new RefreshTokenGrant();

        $this->addGrantType($refreshTokenGrant);
    }
}
