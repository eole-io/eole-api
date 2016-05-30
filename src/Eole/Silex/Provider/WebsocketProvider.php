<?php

namespace Eole\Silex\Provider;

use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Eole\WebSocket\Routing\TopicRoute;

class WebsocketProvider implements ServiceProviderInterface
{
    /**
     * {@InheritDoc}
     */
    public function register(Container $app)
    {
        $app['eole.websocket_topic.chat'] = function () {
            return new \Eole\WebSocket\Topic\ChatTopic('eole/core/chat');
        };

        $gamePartiesFactory = function ($topicPath, array $arguments) {
            return new \Eole\WebSocket\Topic\PartiesTopic($topicPath, $arguments);
        };

        $app['eole.websocket_topic.game_parties.factory'] = $app->protect($gamePartiesFactory);

        $app['eole.websocket_topic.game_parties'] = function () use ($gamePartiesFactory) {
            return $gamePartiesFactory('eole/core/parties', array('game_name' => null));
        };

        $app['eole.websocket.routes']->add('eole_core_chat', new TopicRoute(
            $app['eole.websocket_topic.chat']->getId(),
            $app['eole.websocket_topic.chat']
        ));

        $app['eole.websocket.routes']->add('eole_core_parties', new TopicRoute(
            $app['eole.websocket_topic.game_parties']->getId(),
            $app['eole.websocket_topic.game_parties']
        ));

        $app['eole.websocket.routes']->add('eole_core_game_parties', new TopicRoute(
            'eole/core/game/{game_name}/parties',
            'eole.websocket_topic.game_parties.factory',
            array(),
            array('game_name' => '^[a-z0-9_\-]+$')
        ));
    }
}
