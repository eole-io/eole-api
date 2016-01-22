<?php

namespace Eole\OAuth2;

use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\AuthorizationServer as BaseAuthorizationServer;
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
     * @param string $tokensDir
     */
    public function __construct($tokensDir)
    {
        parent::__construct();

        $this->tokensDir = $tokensDir;

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
            var_dump(['VerifyCredentialsCallback' => func_get_args()]);
            return 5;
        });

        $this->addGrantType($passwordGrant);

        return $this;
    }
}
