<?php

namespace Eole\Silex\OAuth2;

use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Eole\Silex\OAuth2\AuthorizationServer;
use Eole\Silex\OAuth2\ResourceServer;

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
