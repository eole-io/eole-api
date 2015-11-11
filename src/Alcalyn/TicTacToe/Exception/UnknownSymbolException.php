<?php

namespace Alcalyn\TicTacToe\Exception;

class UnknownSymbolException extends \OutOfBoundsException
{
    /**
     * @param string $symbol
     * @param \Exception $previous
     */
    public function __construct($symbol, $previous = null)
    {
        parent::__construct(sprintf('Unknown symbol: "%"', $symbol), 0, $previous);
    }
}
