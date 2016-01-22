<?php

namespace Eole\OAuth2\Storage;

use League\OAuth2\Server\Entity\AccessTokenEntity;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Storage\AccessTokenInterface;
use League\OAuth2\Server\Storage\AbstractStorage;

class AccessToken extends AbstractStorage implements AccessTokenInterface
{
    /**
     * @var string
     */
    private $tokensDir;

    /**
     * @param string $tokensDir
     */
    public function __construct($tokensDir)
    {
        $this->tokensDir = $tokensDir;
    }

    /**
     * {@InheritDoc}
     */
    public function get($token)
    {
        if (!file_exists($this->tokensDir.'/'.$token)) {
            return null;
        }

        $tokenContent = file_get_contents($this->tokensDir.'/'.$token);

        $accessToken = new AccessTokenEntity($this->server);
        $accessToken->setExpireTime(intval($tokenContent));

        return $accessToken;
    }

    /**
     * {@InheritDoc}
     */
    public function getScopes(AccessTokenEntity $token)
    {
        throw new \Eole\OAuth2\Exception\NotImplementedException();
    }

    /**
     * {@InheritDoc}
     */
    public function create($token, $expireTime, $sessionId)
    {
        file_put_contents($this->tokensDir.'/'.$token, $expireTime);
    }

    /**
     * {@InheritDoc}
     */
    public function associateScope(AccessTokenEntity $token, ScopeEntity $scope)
    {
        throw new \Eole\OAuth2\Exception\NotImplementedException();
    }

    /**
     * {@InheritDoc}
     */
    public function delete(AccessTokenEntity $token)
    {
        if (file_exists($this->tokensDir.'/'.$token)) {
            unlink($this->tokensDir.'/'.$token);
        }
    }
}
