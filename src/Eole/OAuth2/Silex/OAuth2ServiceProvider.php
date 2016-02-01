<?php

namespace Eole\OAuth2\Silex;

use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Eole\OAuth2\Security\Http\Firewall\OAuth2Listener;
use Eole\OAuth2\Security\Http\EntryPoint\NoEntryPoint;
use Eole\OAuth2\Security\Authentication\Provider\OAuth2Provider;
use Eole\OAuth2\Storage\Client;
use Eole\OAuth2\Storage\Session;
use Eole\OAuth2\Storage\AccessToken;
use Eole\OAuth2\Storage\Scope;
use Eole\OAuth2\Storage\RefreshToken as RefreshTokenStorage;
use Eole\OAuth2\Grant\Password;
use Eole\OAuth2\Grant\RefreshToken as RefreshTokenGrant;
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
     * @var \stdClass[]
     */
    private $clientsData;

    /**
     * @var string
     */
    private $accessTokensDir;

    /**
     * @var string
     */
    private $refreshTokensDir;

    /**
     * @param string $firewallName
     * @param string $tokensDir
     * @param \stdClass[] $clientsData
     */
    public function __construct($firewallName, $tokensDir, array $clientsData)
    {
        $this->firewallName = $firewallName;
        $this->tokensDir = $tokensDir;
        $this->clientsData = $clientsData;

        $this->accessTokensDir = $this->tokensDir.'/access-tokens';
        $this->refreshTokensDir = $this->tokensDir.'/refresh-tokens';

        $this->touchTokensDir();
    }

    /**
     * {@InheritDoc}
     */
    public function register(Container $app)
    {
        /**
         * Storage
         */
        $app['eole.oauth.storage.session'] = function () {
            return new Session($this->accessTokensDir);
        };

        $app['eole.oauth.storage.access_token'] = function () {
            return new AccessToken($this->accessTokensDir);
        };

        $app['eole.oauth.storage.client'] = function () {
            return new Client($this->clientsData);
        };

        $app['eole.oauth.storage.scope'] = function () {
            return new Scope();
        };

        $app['eole.oauth.storage.refresh_token'] = function () {
            return new RefreshTokenStorage($this->refreshTokensDir);
        };

        /**
         * Grant
         */
        $app['eole.oauth.grant.password'] = function () use ($app) {
            return new Password(
                $app['eole.user_provider'],
                $app['security.encoder_factory']
            );
        };

        $app['eole.oauth.grant.refresh_token'] = function () {
            return new RefreshTokenGrant();
        };

        /**
         * Server
         */
        $app['eole.oauth.authorization_server'] = function () use ($app) {
            return new AuthorizationServer(
                $app['eole.oauth.storage.session'],
                $app['eole.oauth.storage.access_token'],
                $app['eole.oauth.storage.client'],
                $app['eole.oauth.storage.scope'],
                $app['eole.oauth.storage.refresh_token'],
                $app['eole.oauth.grant.password'],
                $app['eole.oauth.grant.refresh_token']
            );
        };

        $app['eole.oauth.resource_server'] = function () use ($app) {
            return new ResourceServer(
                $app['eole.oauth.storage.session'],
                $app['eole.oauth.storage.access_token'],
                $app['eole.oauth.storage.client'],
                $app['eole.oauth.storage.scope']
            );
        };

        /**
         * Security
         */
        $app['security.authentication_listener.factory.oauth'] = $app->protect(function ($name) use ($app) {

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

    /**
     * Check tokens directory exists or create it.
     */
    private function touchTokensDir()
    {
        if (!is_dir($this->accessTokensDir)) {
            mkdir($this->accessTokensDir, 0777, true);
        }

        if (!is_dir($this->refreshTokensDir)) {
            mkdir($this->refreshTokensDir, 0777, true);
        }
    }
}
