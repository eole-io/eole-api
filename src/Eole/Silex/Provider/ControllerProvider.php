<?php

namespace Eole\Silex\Provider;

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
        $app['eole.converter.game'] = function () use ($app) {
            return new \Eole\Core\Converter\GameConverter(
                $app['orm.em']->getRepository('Eole:Game')
            );
        };

        $app['eole.converter.party'] = function () use ($app) {
            return new \Eole\Core\Converter\PartyConverter(
                $app['orm.em']->getRepository('Eole:Party')
            );
        };

        $app['eole.controller.player'] = function () use ($app) {
            return new \Eole\Core\Controller\PlayerController(
                $app['eole.player_api']
            );
        };

        $app['eole.controller.game'] = function () use ($app) {
            return new \Eole\Core\Controller\GameController(
                $app['orm.em']->getRepository('Eole:Game')
            );
        };

        $app['eole.controller.party'] = function () use ($app) {
            return new \Eole\Core\Controller\PartyController(
                $app['orm.em']->getRepository('Eole:Party'),
                $app['orm.em'],
                $app['eole.party_manager'],
                $app['dispatcher']
            );
        };

        $app->before(function (\Symfony\Component\HttpFoundation\Request $request, Application $app) {
            if (null !== $app['user']) {
                $app['eole.controller.player']->setLoggedUser($app['user']);
                $app['eole.controller.party']->setLoggedPlayer($app['user']);
            }
        });

        $app->forwardEventsToPushServer(array(
            \Eole\Core\Event\PartyEvent::CREATE_AFTER,
            \Eole\Core\Event\SlotEvent::JOIN_AFTER,
            \Eole\Core\Event\PartyEvent::STARTED,
            \Eole\Core\Event\PartyEvent::ENDED,
        ));
    }

    /**
     * {@InheritDoc}
     */
    public function connect(Application $app)
    {
        $app->mount('api', new \Eole\RestApi\ControllerProvider\PlayerControllerProvider());
        $app->mount('api', new \Eole\RestApi\ControllerProvider\GameControllerProvider());
        $app->mount('api', new \Eole\RestApi\ControllerProvider\PartyControllerProvider());

        return $app['controllers_factory'];
    }
}
