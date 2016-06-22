<?php

namespace Eole\Silex\Tests\stubs\OAuth;

use League\OAuth2\Server\Entity\SessionEntity;
use League\OAuth2\Server\Entity\ClientEntity;
use League\OAuth2\Server\Storage\ClientInterface;
use League\OAuth2\Server\Storage\AbstractStorage;

class ClientStorageMock extends AbstractStorage implements ClientInterface
{
    /**
     * {@InheritDoc}
     */
    public function get($clientId, $clientSecret = null, $redirectUri = null, $grantType = null)
    {
        if (('client-id' === $clientId) && ('client-secret' === $clientSecret)) {
            $clientEntity = new ClientEntity($this->server);

            return $clientEntity->hydrate([
                'id' => $clientId,
                'secret' => $clientSecret,
            ]);
        }

        return null;
    }

    /**
     * {@InheritDoc}
     */
    public function getBySession(SessionEntity $session)
    {
        throw new \Eole\Sandstone\OAuth2\Exception\NotImplementedException();
    }
}
