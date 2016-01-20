<?php

namespace Eole\Silex\OAuth2\Storage;

use League\OAuth2\Server\Entity\SessionEntity;
use League\OAuth2\Server\Entity\ClientEntity;
use League\OAuth2\Server\Storage\ClientInterface;
use League\OAuth2\Server\Storage\AbstractStorage;

class Client extends AbstractStorage implements ClientInterface
{
    /**
     * {@InheritDoc}
     */
    public function get($clientId, $clientSecret = null, $redirectUri = null, $grantType = null)
    {
        $eoleAngularClient = new ClientEntity($this->server);

        $eoleAngularClient->hydrate(array(
            'id' => 'eole-angular',
            'secret' => 'eole-angular-secret',
            'name' => 'eole-angular-name',
        ));

        if (($eoleAngularClient->getId() === $clientId) && ($eoleAngularClient->getSecret() === $clientSecret)) {
            return $eoleAngularClient;
        }

        return null;
    }

    /**
     * {@InheritDoc}
     */
    public function getBySession(SessionEntity $session)
    {
    }
}
