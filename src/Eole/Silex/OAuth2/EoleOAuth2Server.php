<?php

namespace Eole\Silex\OAuth2;

use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\AuthorizationServer;
use Eole\Silex\OAuth2\Storage\Client;
use Eole\Silex\OAuth2\Storage\Session;
use Eole\Silex\OAuth2\Storage\AccessToken;

class EoleOAuth2Server extends AuthorizationServer
{
    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @param string $cacheDir
     */
    public function __construct($cacheDir)
    {
        parent::__construct();

        $this->cacheDir = $cacheDir;

        $this->init();
    }

    /**
     * @return self
     */
    private function init()
    {
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }

        $this
            ->setClientStorage(new Client())
            ->setSessionStorage(new Session())
            ->setAccessTokenStorage(new AccessToken($this->cacheDir))
            //->setScopeStorage(new ScopeStorage())
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
