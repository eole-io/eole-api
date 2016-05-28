<?php

namespace Eole\Silex\Provider;

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
            $app['project.root'].'/src/Eole/Core/Serializer'
        );

        $app->extend('eole.mappings', function ($mappings, $app) {
            $mappings []= array(
                'type' => 'yml',
                'namespace' => 'Eole\Core\Model',
                'path' => $app['project.root'].'/src/Eole/Core/Mapping',
                'alias' => 'Eole',
            );

            return $mappings;
        });

        $app['eole.player_api'] = function () use ($app) {
            return new \Eole\Core\Service\PlayerApi(
                $app['eole.player_manager'],
                $app['orm.em']->getRepository('Eole:Player')
            );
        };

        $app['eole.player_manager'] = function () use ($app) {
            $encoderFactory = $app['security.encoder_factory'];
            $userClass = \Eole\Core\Model\Player::class;

            return new \Eole\Core\Service\PlayerManager(
                $encoderFactory,
                $userClass
            );
        };

        $app['eole.party_manager'] = function () use ($app) {
            return new \Eole\Core\Service\PartyManager(
                $app['dispatcher']
            );
        };
    }
}
