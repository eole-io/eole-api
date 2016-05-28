<?php

namespace Eole\Silex;

class EoleMod extends Mod
{
    /**
     * {@InheritDoc}
     */
    public function createServiceProvider()
    {
        return new Provider\ServiceProvider();
    }

    /**
     * {@InheritDoc}
     */
    public function createControllerProvider()
    {
        return new Provider\ControllerProvider();
    }

    /**
     * {@InheritDoc}
     */
    public function createWebsocketProvider()
    {
        return new Provider\WebsocketProvider();
    }
}
