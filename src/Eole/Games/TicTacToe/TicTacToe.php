<?php

namespace Eole\Games\TicTacToe;

use Eole\Core\Model\Game;
use Eole\Silex\GameProvider;

class TicTacToe extends GameProvider
{
    /**
     * {@InheritDoc}
     */
    public function createGame()
    {
        $game = new Game();

        $game->setName('tictactoe');

        return $game;
    }

    /**
     * {@InheritDoc}
     */
    public function createServiceProvider()
    {
        return new TicTacToeProvider();
    }

    /**
     * {@InheritDoc}
     */
    public function createWebsocketProvider()
    {
        return new TicTacToeWebsocketProvider();
    }
}
