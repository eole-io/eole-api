<?php

namespace Eole\Games\MyGame;

use Eole\Core\Model\Game;
use Eole\Silex\GameProvider;

class MyGame extends GameProvider
{
    /**
     * @var string
     */
    const GAME_NAME = 'my-game';

    /**
     * {@InheritDoc}
     */
    public function createGame()
    {
        $game = new Game();

        $game->setName('my-game');

        return $game;
    }

    /**
     * {@InheritDoc}
     */
    public function createControllerProvider()
    {
        return new ControllerProvider();
    }
}
