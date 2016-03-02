<?php

namespace Eole\Silex;

abstract class GameAdapter implements GameInterface
{
    /**
     * {@InheritDoc}
     */
    public function installGame()
    {
        // noop
    }

    /**
     * {@InheritDoc}
     */
    public function createServiceProvider()
    {
        return null;
    }

    /**
     * {@InheritDoc}
     */
    public function createControllerProvider()
    {
        return null;
    }

    /**
     * {@InheritDoc}
     */
    public function createWebsocketProvider()
    {
        return null;
    }
}
