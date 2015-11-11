<?php

namespace Eole\Silex\ControllerProvider;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;

class GameControllerProvider implements ControllerProviderInterface
{
    /**
     * @param Application $app
     */
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $gameController = 'eole.controller.game';

        $controllers->get('/games', $gameController.':getGames');
        $controllers->get('/games/{name}', $gameController.':getGameByName');

        return $controllers;
    }
}
