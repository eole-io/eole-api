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
            new Session($tokensDir.'/access-tokens'),
            new AccessToken($tokensDir.'/access-tokens'),
            new Client(),
            new Scope()
        );
    }
}
