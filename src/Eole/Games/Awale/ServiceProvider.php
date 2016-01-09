<?php

namespace Eole\Games\Awale;

use Pimple\ServiceProviderInterface;
use Pimple\Container;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * {@InheritDoc}
     */
    public function register(Container $app)
    {
        $app['serializer.builder']->addMetadataDir(
            __DIR__.'/serializer',
            'Alcalyn\\Awale'
        );

        $app['serializer.builder']->addMetadataDir(
            __DIR__.'/serializer',
            'Eole\\Games\\Awale\\Model'
        );

        $app->extend('eole.mappings', function ($mappings, $app) {
            $mappings []= array(
                'type' => 'yml',
                'namespace' => 'Alcalyn\\Awale',
                'path' => $app['project.root'].'/src/Eole/Games/Awale/Mapping',
                'alias' => 'Awale',
            );

            $mappings []= array(
                'type' => 'yml',
                'namespace' => 'Eole\\Games\\Awale\\Model',
                'path' => $app['project.root'].'/src/Eole/Games/Awale/Mapping',
                'alias' => 'EoleAwale',
            );

            return $mappings;
        });
    }
}
