<?php

namespace Eole\Games\Awale;

use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Eole\Games\Awale\Event\AwaleEvent;
use Eole\Games\Awale\EventListener;

class ControllerProvider implements ServiceProviderInterface, ControllerProviderInterface
{
    /**
     * {@InheritDoc}
     */
    public function register(Container $app)
    {
        $app['eole.games.awale.controller'] = function () use ($app) {
            return new Controller(
                $app['orm.em']->getRepository('EoleAwale:AwaleParty'),
                $app['orm.em'],
                $app['eole.party_manager'],
                $app['dispatcher']
            );
        };

        $app->before(function () use ($app) {
            if (null !== $app['user']) {
                $app['eole.games.awale.controller']->setLoggedPlayer($app['user']);
            }

            $app['dispatcher']->addSubscriber(new EventListener(
                $app['orm.em'],
                $app['eole.party_manager']
            ));
        });

        $app->forwardEventToPushServer(AwaleEvent::PLAY);
    }

    /**
     * {@InheritDoc}
     */
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $awaleController = 'eole.games.awale.controller';

        $controllers->get('/test', $awaleController.':getTest');
        $controllers->get('/find-by-id/{id}', $awaleController.':findById');
        $controllers->post('/play', $awaleController.':play');

        return $controllers;
    }
}
