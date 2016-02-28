<?php

namespace Eole\Silex;

use Pimple\ServiceProviderInterface;
use Silex\Api\ControllerProviderInterface;
use Eole\Core\Model\Game;

interface GameInterface
{
    /**
     * @return Game instance of game
     */
    public function createGame();

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
