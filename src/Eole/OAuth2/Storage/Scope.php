<?php

namespace Eole\OAuth2\Storage;

use League\OAuth2\Server\Storage\ScopeInterface;
use League\OAuth2\Server\Storage\AbstractStorage;

class Scope extends AbstractStorage implements ScopeInterface
{
    /**
     * {@InheritDoc}
     */
    public function get($scope, $grantType = null, $clientId = null)
    {
        throw new \Eole\OAuth2\Exception\NotImplementedException();
    }
}
