<?php

namespace Alcalyn\TicTacToe\Exception;

class PlayingTwiceException extends TicTacToeException
{
    public function __construct($previous = null)
    {
        parent::__construct('Not your turn to play.', $previous);
    }
}
