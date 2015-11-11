<?php

namespace Alcalyn\TicTacToe\Exception;

class InvalidCoordsException extends \RangeException
{
    /**
     * @param string $name 'col' or 'row'
     * @param int $coords
     * @param \Exception $previous
     */
    public function __construct($name, $coords, $previous = null)
    {
        parent::__construct(
            sprintf('Invalid range for %s, must be in [0,2], got "%d".', $name, $coords),
            0,
            $previous
        );
    }
}
