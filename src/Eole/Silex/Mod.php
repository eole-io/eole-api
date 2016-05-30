<?php

namespace Eole\Silex;

use Pimple\ServiceProviderInterface;
use Silex\Api\ControllerProviderInterface;

abstract class Mod
{
    /**
     * Returns a service provider which will be register in Silex stack.
     *
     * @return null|ServiceProviderInterface
     */
    public function createServiceProvider()
    {
        return null;
    }

    /**
     * Returns a controller provider which mount routes in Rest Api stack.
     *
     * If the provider also implements ServiceProviderInterface,
     * it will be registered in Rest Api stack.
     *
     * @return null|ControllerProviderInterface
     */
    public function createControllerProvider()
    {
        return null;
    }

    /**
     * Returns a service provider which register topics in Websocket stack.
     *
     * @return null|ServiceProviderInterface
     */
    public function createWebsocketProvider()
    {
        return null;
    }
}
