<?php

namespace Eole\OAuth2\Silex;

use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Eole\OAuth2\Security\Http\Firewall\OAuth2Listener;
use Eole\OAuth2\Security\Authentication\Provider\OAuth2Provider;
use Eole\OAuth2\Security\Http\EntryPoint\NoEntryPoint;
use Eole\OAuth2\AuthorizationServer;
use Eole\OAuth2\ResourceServer;

class OAuth2ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string
     */
    private $firewallName;

    /**
     * @var string
     */
    private $tokensDir;

    /**
     * @param string $firewallName
     * @param string $tokensDir
     */
    public function __construct($firewallName, $tokensDir)
    {
        $this->firewallName = $firewallName;
        $this->tokensDir = $tokensDir;
    }

    /**
     * {@InheritDoc}
     */
    public function register(Container $app)
    {
        $app['eole.oauth.authorization_server'] = function () use ($app) {
            return new AuthorizationServer($this->tokensDir, $app['eole.user_provider'], $app['security.encoder_factory']);
        };

        $app['eole.oauth.resource_server'] = function () {
            return new ResourceServer($this->tokensDir);
        };

        $app['security.authentication_listener.factory.oauth'] = $app->protect(function ($name, $options) use ($app) {

            // define the authentication provider object
            $app['security.authentication_provider.'.$name.'.oauth'] = function () use ($app) {
                return new OAuth2Provider(
                    $app['security.user_provider.'.$this->firewallName],
                    $app['security.user_checker'],
                    $app['eole.oauth.resource_server']
                );
            };

            // define the authentication listener object
            $app['security.authentication_listener.'.$name.'.oauth'] = function () use ($app) {
                return new OAuth2Listener(
                    $app['security.token_storage'],
                    $app['security.authentication_manager'],
                    $app['eole.oauth.resource_server']
                );
            };

            // define the entry point object
            $app['security.entry_point.'.$name.'.oauth'] = function () {
                return new NoEntryPoint();
            };

            return array(
                // the authentication provider id
                'security.authentication_provider.'.$name.'.oauth',
                // the authentication listener id
                'security.authentication_listener.'.$name.'.oauth',
                // the entry point id
                'security.entry_point.'.$name.'.oauth',
                // the position of the listener in the stack
                'pre_auth'
            );
        });
    }
}
