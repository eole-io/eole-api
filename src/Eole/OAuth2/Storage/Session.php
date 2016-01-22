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
     * {@InheritDoc}
     */
    public function getByAccessToken(AccessTokenEntity $accessToken)
    {
        throw new \Eole\OAuth2\Exception\NotImplementedException();
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
        throw new \Eole\OAuth2\Exception\NotImplementedException();
    }

    /**
     * {@InheritDoc}
     */
    public function associateScope(SessionEntity $session, ScopeEntity $scope)
    {
        throw new \Eole\OAuth2\Exception\NotImplementedException();
    }
}
