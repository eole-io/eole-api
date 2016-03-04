<?php

namespace Eole\Silex;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Eole\Silex\Application as SilexApplication;
use Eole\Core\Model\Game;

abstract class GameProvider implements ServiceProviderInterface
{
    /**
     * @return Game instance of game
     */
    public abstract function createGame();

    /**
     * Persist game fixtures.
     *
     * @param SilexApplication $app
     * @param ObjectManager $om
     */
    public function createFixtures(SilexApplication $app, ObjectManager $om)
    {
        // noop
    }

    /**
     * Register game services.
     *
     * {@InheritDoc}
     */
    public function register(Container $app)
    {
        // noop
    }

    /**
     * Returns a controller provider which mount game rest api.
     *
     * @return null|ControllerProviderInterface
     */
    public function createControllerProvider()
    {
        return null;
    }

    /**
     * Returns a service provider which register websocket topics.
     *
     * @return null|ServiceProviderInterface
     */
    public function createWebsocketProvider()
    {
        return null;
    }
}
