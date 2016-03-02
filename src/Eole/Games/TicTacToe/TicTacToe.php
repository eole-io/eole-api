<?php

namespace Eole\Games\TicTacToe;

use Eole\Core\Model\Game;
use Eole\Silex\GameAdapter;

class TicTacToe extends GameAdapter
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
    public function createControllerProvider()
    {
        return null;
    }

    /**
     * {@InheritDoc}
     */
    public function createWebsocketProvider()
    {
        return new TicTacToeWebsocketProvider();
    }
}
