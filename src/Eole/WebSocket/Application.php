<?php

namespace Eole\Websocket;

use Eole\Silex\Application as BaseApplication;

class Application extends BaseApplication
{
    /**
     * {@InheritDoc}
     */
    public function __construct(array $values = array())
    {
        parent::__construct($values);

        $this->loadAllWebsocketTopics();
    }

    /**
     * Register Eole and games websocket topics.
     */
    private function loadAllWebsocketTopics()
    {
        foreach ($this['environment']['mods'] as $modName => $modConfig) {
            $modClass = $modConfig['provider'];
            $mod = new $modClass();
            $provider = $mod->createWebsocketProvider();

            if ($provider instanceof \Pimple\ServiceProviderInterface) {
                $this->register($provider);
            }
        }
    }
}
