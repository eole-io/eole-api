<?php

namespace Eole\Games\Awale;

use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Eole\Games\Awale\AwaleTopic;

class WebsocketProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app
            ->topic('eole/games/awale/parties/{party_id}', function ($topicPattern, $arguments) {
                return new AwaleTopic($topicPattern, intval($arguments['party_id']));
            })
            ->assert('party_id', '^[0-9]+$')
        ;
    }
}
