<?php

namespace Eole\Games\MyGame;

use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Eole\Core\Event\PartyEvent;

class ControllerProvider implements ServiceProviderInterface
{
    /**
     * {@InheritDoc}
     */
    public function register(Container $app)
    {
        $app->before(function () use ($app) {
            $app->on(
                PartyEvent::CREATE_BEFORE,
                [$app['eole.games.my-game.listener.party'], 'onPartyCreateBefore']
            );
        });

        $app['eole.games.my-game.listener.party'] = function () use ($app) {
            return new PartyListener($app['eole.party_manager']);
        };
    }
}
