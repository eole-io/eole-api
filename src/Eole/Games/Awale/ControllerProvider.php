<?php

namespace Eole\Games\Awale;

use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;

class ControllerProvider implements ServiceProviderInterface, ControllerProviderInterface
{
    /**
     * {@InheritDoc}
     */
    public function register(Container $app)
    {
        $app['eole.games.awale.controller'] = function () {
            return new Controller();
        };
    }

    /**
     * {@InheritDoc}
     */
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $awaleController = 'eole.games.awale.controller';

        $controllers->get('/test', $awaleController.':getTest');

        return $controllers;
    }
}
