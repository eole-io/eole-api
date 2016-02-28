<?php

namespace Eole\Games\Awale;

use Eole\Core\Model\Game;
use Eole\Silex\GameInterface;

class Awale implements GameInterface
{
    /**
     * {@InheritDoc}
     */
    public function createGame()
    {
        $game = new Game();

        $game->setName('awale');

        return $game;
    }

    /**
     * {@InheritDoc}
     */
    public function createServiceProvider()
    {
        return new ServiceProvider();
    }

    /**
     * {@InheritDoc}
     */
    public function createControllerProvider()
    {
        return new ControllerProvider();
    }

    /**
     * {@InheritDoc}
     */
    public function createWebsocketProvider()
    {
        return new WebsocketProvider();
    }
}
