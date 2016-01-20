<?php

namespace Eole\Silex\OAuth2\Storage;

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
    }

    /**
     * {@InheritDoc}
     */
    public function getByAuthCode(AuthCodeEntity $authCode)
    {
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
    }

    /**
     * {@InheritDoc}
     */
    public function associateScope(SessionEntity $session, ScopeEntity $scope)
    {
    }
}
