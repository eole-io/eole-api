<?php

namespace Eole\Silex\OAuth2\Storage;

use League\OAuth2\Server\Entity\AccessTokenEntity;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Storage\AccessTokenInterface;
use League\OAuth2\Server\Storage\AbstractStorage;

class AccessToken extends AbstractStorage implements AccessTokenInterface
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
        $this->cacheDir = $cacheDir;
    }

    /**
     * {@InheritDoc}
     */
    public function get($token)
    {
    }

    /**
     * {@InheritDoc}
     */
    public function getScopes(AccessTokenEntity $token)
    {
    }

    /**
     * {@InheritDoc}
     */
    public function create($token, $expireTime, $sessionId)
    {
        file_put_contents($this->cacheDir.'/'.$token, $expireTime);
    }

    /**
     * {@InheritDoc}
     */
    public function associateScope(AccessTokenEntity $token, ScopeEntity $scope)
    {
    }

    /**
     * {@InheritDoc}
     */
    public function delete(AccessTokenEntity $token)
    {
    }
}
