<?php

namespace Eole\Games\Awale;

use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Eole\Core\Event\PartyEvent;
use Eole\Games\Awale\EventListener;

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

        $app->before(function () use ($app) {
            $app['dispatcher']->addSubscriber(new EventListener(
                $app['orm.em']->getRepository('EoleAwale:AwaleParty'),
                $app['orm.em']
            ));
        });
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
