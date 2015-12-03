<?php

namespace Eole\RestApi;

use Eole\Silex\Application as BaseApplication;

class Application extends BaseApplication
{
    /**
     * {@InheritDoc}
     */
    public function __construct(array $values = array())
    {
        parent::__construct($values);

        $this->registerServices();
        $this->registerEventListeners();
        $this->mountApi();
    }

    /**
     * Register RestApi services
     */
    private function registerServices()
    {
        $this['eole.converter.game'] = function () {
            return new \Eole\Core\Converter\GameConverter(
                $this['orm.em']->getRepository('Eole:Game')
            );
        };

        $this['eole.controller.player'] = function () {
            return new \Eole\Core\Controller\PlayerController(
                $this['eole.player_api'],
                $this['eole.player_manager']
            );
        };

        $this['eole.controller.game'] = function () {
            return new \Eole\Core\Controller\GameController(
                $this['orm.em']->getRepository('Eole:Game')
            );
        };

        $this['eole.controller.party'] = function () {
            return new \Eole\Core\Controller\PartyController(
                $this['orm.em']->getRepository('Eole:Party'),
                $this['orm.em'],
                $this['eole.party_manager']
            );
        };

        $this['eole.event_serializer'] = function () {
            return new Service\EventSerializer();
        };

        $this['eole.push_server'] = function () {
            $pushServerPort = $this['environment']['push_server']['server']['port'];

            $context = new \ZMQContext();
            $socket = $context->getSocket(\ZMQ::SOCKET_PUSH);
            $socket->connect('tcp://127.0.0.1:'.$pushServerPort);

            return $socket;
        };
    }

    private function registerEventListeners()
    {
        $this['dispatcher']->addSubscriber(new EventListener\EventToSocketListener(
            $this['eole.push_server'],
            $this['eole.event_serializer']
        ));
    }

    /**
     * Mount /api
     */
    private function mountApi()
    {
        $this->mount('api', new ControllerProvider\PlayerControllerProvider());
        $this->mount('api', new ControllerProvider\GameControllerProvider());
        $this->mount('api', new ControllerProvider\PartyControllerProvider());
    }
}
