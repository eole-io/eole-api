<?php

namespace Eole\Silex\OAuth2;

use League\OAuth2\Server\ResourceServer as BaseResourceServer;
use Eole\Silex\OAuth2\Storage\Client;
use Eole\Silex\OAuth2\Storage\Session;
use Eole\Silex\OAuth2\Storage\AccessToken;
use Eole\Silex\OAuth2\Storage\Scope;

class ResourceServer extends BaseResourceServer
{
    /**
     * @param string $tokensDir
     */
    public function __construct($tokensDir)
    {
        parent::__construct(
            new Session(),
            new AccessToken($tokensDir),
            new Client(),
            new Scope()
        );
    }
}
