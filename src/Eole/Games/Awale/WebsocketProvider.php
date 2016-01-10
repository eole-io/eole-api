<?php

namespace Eole\Games\Awale;

use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Eole\WebSocket\Routing\TopicRoute;
use Eole\Games\Awale\AwaleTopic;

class WebsocketProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $awaleTopicFactory = function ($topicPath, array $arguments) use ($app) {
            return new AwaleTopic($topicPath);
        };

        $app['eole.games.awale.topic.factory'] = $app->protect($awaleTopicFactory);

        $app['eole.websocket.routes']->add('eole_games_awale_party', new TopicRoute(
            'eole/games/awale/parties/{party_id}',
            'eole.games.awale.topic.factory',
            array(),
            array('party_id' => '^[0-9]+$')
        ));
    }
}
