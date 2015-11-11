<?php

namespace Alcalyn\TicTacToe\Exception;

class InvalidMoveException extends \LogicException
{
    /**
     * @param string $reason
     * @param \Exception $previous
     */
    public function __construct($reason, $previous = null)
    {
        parent::__construct($reason, 0, $previous);
    }
}
