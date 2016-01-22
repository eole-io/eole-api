<?php

namespace Eole\OAuth2;

use League\OAuth2\Server\ResourceServer as BaseResourceServer;
use Eole\OAuth2\Storage\Client;
use Eole\OAuth2\Storage\Session;
use Eole\OAuth2\Storage\AccessToken;
use Eole\OAuth2\Storage\Scope;

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
