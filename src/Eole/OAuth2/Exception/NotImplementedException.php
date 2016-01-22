<?php

namespace Eole\OAuth2\Exception;

class NotImplementedException extends \BadMethodCallException
{
    public function __construct(\Exception $previous = null)
    {
        parent::__construct('This part of OAuth2 implementation is not yet implemented.', 0, $previous);
    }
}
