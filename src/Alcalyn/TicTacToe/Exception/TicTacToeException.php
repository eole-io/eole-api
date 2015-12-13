<?php

namespace Alcalyn\TicTacToe\Exception;

class TicTacToeException extends \Exception
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
