<?php

namespace Alcalyn\Awale\Exception;

class AwaleException extends \Exception
{
    /**
     * @param string $message
     * @param \Exception $previous
     */
    public function __construct($message = '', $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
