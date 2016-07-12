<?php

namespace Eole\Silex\Provider;

use Pimple\ServiceProviderInterface;
use Pimple\Container;

class WebsocketProvider implements ServiceProviderInterface
{
    /**
     * {@InheritDoc}
     */
    public function register(Container $app)
    {
        $app->topic('eole/core/chat', function ($topicPattern) {
            return new \Eole\Websocket\Topic\ChatTopic($topicPattern);
        });

        $app->topic('eole/core/parties', function ($topicPattern) {
            return new \Eole\Websocket\Topic\PartiesTopic($topicPattern);
        });

        $app
            ->topic('eole/core/game/{game_name}/parties', function ($topicPattern, $arguments) {
                return new \Eole\Websocket\Topic\PartiesTopic($topicPattern, $arguments);
            })
            ->assert('game_name', '^[a-z0-9_\-]+$')
        ;
    }
}
