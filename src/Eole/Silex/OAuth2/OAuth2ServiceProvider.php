<?php

namespace Eole\Silex\OAuth2;

use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Eole\Silex\OAuth2\EoleOAuth2Server;

class OAuth2ServiceProvider implements ServiceProviderInterface
{
    /**
     * {@InheritDoc}
     */
    public function register(Container $app)
    {
        $app['eole.oauth.server'] = function () use ($app) {
            return new EoleOAuth2Server($app['project.root'].'/var/cache/oauth-tokens');
        };
    }
}
