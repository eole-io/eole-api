<?php

namespace Eole\Games\TicTacToe;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class TicTacToeProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['serializer.builder']->addMetadataDir(
            __DIR__.'/serializer',
            'Alcalyn\\TicTacToe'
        );
    }
}
