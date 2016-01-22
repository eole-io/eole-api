<?php

namespace Eole\OAuth2\Silex;

use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Eole\OAuth2\AuthorizationServer;
use Eole\OAuth2\ResourceServer;

class OAuth2ServiceProvider implements ServiceProviderInterface
{
    /**
     * {@InheritDoc}
     */
    public function register(Container $app)
    {
        $tokensDir = $app['project.root'].'/var/oauth-tokens';

        $app['eole.oauth.authorization_server'] = function () use ($tokensDir) {
            return new AuthorizationServer($tokensDir);
        };

        $app['eole.oauth.resource_server'] = function () use ($tokensDir) {
            return new ResourceServer($tokensDir);
        };
    }
}
