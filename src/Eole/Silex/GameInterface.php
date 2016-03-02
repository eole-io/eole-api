<?php

namespace Eole\Silex;

use Pimple\ServiceProviderInterface;
use Silex\Api\ControllerProviderInterface;
use Eole\Core\Model\Game;

interface GameInterface
{
    /**
     * @return Game instance of game.
     */
    public function createGame();

    /**
     * Install game.
     */
    public function installGame();

    /**
     * @return null|ServiceProviderInterface
     */
    public function createServiceProvider();

    /**
     * @return null|ControllerProviderInterface
     */
    public function createControllerProvider();

    /**
     * @return null|ServiceProviderInterface
     */
    public function createWebsocketProvider();
}
