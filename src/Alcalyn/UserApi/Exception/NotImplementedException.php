<?php

namespace Alcalyn\UserApi\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class NotImplementedException extends HttpException
{
    /**
     * @param string $message
     */
    public function __construct($message = null)
    {
        parent::__construct(501, $message);
    }
}
