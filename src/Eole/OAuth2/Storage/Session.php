<?php

namespace Eole\OAuth2\Storage;

use League\OAuth2\Server\Entity\SessionEntity;
use League\OAuth2\Server\Entity\AccessTokenEntity;
use League\OAuth2\Server\Entity\AuthCodeEntity;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Storage\AbstractStorage;
use League\OAuth2\Server\Storage\SessionInterface;

class Session extends AbstractStorage implements SessionInterface
{
    /**
     * @var string
     */
    private $accessTokensDir;

    /**
     * @param string $accessTokensDir
     */
    public function __construct($accessTokensDir)
    {
        $this->accessTokensDir = $accessTokensDir;
    }

    /**
     * {@InheritDoc}
     */
    public function getByAccessToken(AccessTokenEntity $accessToken)
    {
        if (!file_exists($this->accessTokensDir.'/'.$accessToken->getId())) {
            return null;
        }

        $tokenContent = explode('-', file_get_contents($this->accessTokensDir.'/'.$accessToken->getId()));
        $sessionId = $tokenContent[0];

        $session = new SessionEntity($this->server);
        $session->setId($sessionId);

        return $session;
    }

    /**
     * {@InheritDoc}
     */
    public function getByAuthCode(AuthCodeEntity $authCode)
    {
        throw new \Eole\OAuth2\Exception\NotImplementedException();
    }

    /**
     * {@InheritDoc}
     */
    public function getScopes(SessionEntity $session)
    {
        $eoleScope = new ScopeEntity($this->server);

        $eoleScope->hydrate(array(
            'id' => 'eole-scope',
            'description' => 'Eole scope.',
        ));

        return array($eoleScope);
    }

    /**
     * {@InheritDoc}
     */
    public function create($ownerType, $ownerId, $clientId, $clientRedirectUri = null)
    {
        return $ownerId;
    }

    /**
     * {@InheritDoc}
     */
    public function associateScope(SessionEntity $session, ScopeEntity $scope)
    {
        $session->associateScope($scope);
    }
}
