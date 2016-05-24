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
        $this->handleProdErrors();
    }

    /**
     * Register RestApi services
     */
    private function registerServices()
    {
        $this['eole.api_response_filter'] = function () {
            return new \Eole\Core\Service\ApiResponseFilter(
                $this['serializer']
            );
        };

        $this['eole.converter.game'] = function () {
            return new \Eole\Core\Converter\GameConverter(
                $this['orm.em']->getRepository('Eole:Game')
            );
        };

        $this['eole.converter.party'] = function () {
            return new \Eole\Core\Converter\PartyConverter(
                $this['orm.em']->getRepository('Eole:Party')
            );
        };

        $this['eole.controller.player'] = function () {
            return new \Eole\Core\Controller\PlayerController(
                $this['eole.player_api']
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
                $this['eole.party_manager'],
                $this['dispatcher']
            );
        };

        $this['eole.push_server'] = function () {
            $pushServerPort = $this['environment']['push_server']['server']['port'];

            $context = new \ZMQContext();
            $socket = $context->getSocket(\ZMQ::SOCKET_PUSH);
            $socket->connect('tcp://127.0.0.1:'.$pushServerPort);

            return $socket;
        };

        $this->register(new \Eole\OAuth2\Silex\OAuth2ControllerProvider());

        $this->before(function (\Symfony\Component\HttpFoundation\Request $request, BaseApplication $app) {
            if (null !== $app['user']) {
                $app['eole.controller.player']->setLoggedUser($app['user']);
                $app['eole.controller.party']->setLoggedPlayer($app['user']);
            }
        });
    }

    private function registerEventListeners()
    {
        $corsOrigin = $this['environment']['cors']['access_control_allow_origin'];

        if ($corsOrigin) {
            $this->register(new \JDesrosiers\Silex\Provider\CorsServiceProvider(), array(
                'cors.allowOrigin' => $corsOrigin,
            ));

            $this->after($this['cors']);
        }

        $this['eole.listener.api_response_filter'] = function () {
            return new \Eole\Core\EventListener\ApiResponseFilterListener(
                $this['eole.api_response_filter']
            );
        };

        $this['eole.listener.event_to_socket'] = function () {
            return new EventListener\EventToSocketListener(
                $this['eole.push_server'],
                $this['eole.event_serializer'],
                $this['environment']['push_server']['enabled']
            );
        };

        $this->on(\Symfony\Component\HttpKernel\KernelEvents::VIEW, function ($event) {
            $this['eole.listener.api_response_filter']->onKernelView($event);
        });

        $this->forwardEventToPushServer(\Eole\Core\Event\PartyEvent::CREATE_AFTER);
        $this->forwardEventToPushServer(\Eole\Core\Event\SlotEvent::JOIN_AFTER);
        $this->forwardEventToPushServer(\Eole\Core\Event\PartyEvent::STARTED);
        $this->forwardEventToPushServer(\Eole\Core\Event\PartyEvent::ENDED);
    }

    /**
     * Automatically forward rest API event to push server.
     *
     * @param string $eventName
     *
     * @return self
     */
    public function forwardEventToPushServer($eventName)
    {
        if (!$this['environment']['push_server']['enabled']) {
            return $this;
        }

        $this->before(function () use ($eventName) {
            $this['dispatcher']->addListener(
                $eventName,
                array($this['eole.listener.event_to_socket'], 'sendEventToSocket')
            );
        });

        return $this;
    }

    /**
     * Automatically forward rest API events to push server.
     *
     * @param string[] $eventsNames
     *
     * @return self
     */
    public function forwardEventsToPushServer(array $eventsNames)
    {
        foreach ($eventsNames as $eventName) {
            $this->forwardEventToPushServer($eventName);
        }

        return $this;
    }

    /**
     * Mount /api
     */
    private function mountApi()
    {
        $this->mount('api', new ControllerProvider\PlayerControllerProvider());
        $this->mount('api', new ControllerProvider\GameControllerProvider());
        $this->mount('api', new ControllerProvider\PartyControllerProvider());

        $this->mount('oauth', new \Eole\OAuth2\Silex\OAuth2ControllerProvider());
    }

    /**
     * Mount a game controller provider.
     * If the provider also implements ServiceProviderInterface, it is registered.
     *
     * @param string $gameName
     * @param \Eole\Silex\GameProvider $gameProvider
     *
     * @return self
     */
    private function mountGame($gameName, $gameProvider)
    {
        $controllerProvider = $gameProvider->createControllerProvider();

        if (null !== $controllerProvider) {
            if ($controllerProvider instanceof \Pimple\ServiceProviderInterface) {
                $this->register($controllerProvider);
            }

            if (!$controllerProvider instanceof \Silex\Api\ControllerProviderInterface) {
                throw new \LogicException(sprintf(
                    'Game controller provider class (%s) for game %s must implement %s.',
                    get_class($controllerProvider),
                    $gameName,
                    \Silex\Api\ControllerProviderInterface::class
                ));
            }

            $this->mount('api/games/'.self::gameNameToUrl($gameName), $controllerProvider);
        }

        return $this;
    }

    /**
     * @param string $gameName
     *
     * @return string
     */
    private static function gameNameToUrl($gameName)
    {
        return str_replace('_', '-', $gameName);
    }

    /**
     * {@InheritDoc}
     */
    public function loadGame($gameName)
    {
        $gameProvider = parent::loadGame($gameName);

        $this->mountGame($gameName, $gameProvider);

        return $gameProvider;
    }

    /**
     * Display serialized errors in prod environment.
     */
    private function handleProdErrors()
    {
        $this->error(function (\Exception $e) {
            if (true === $this['debug']) {
                return;
            }

            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                $errorData = array(
                    'status_code' => $e->getStatusCode(),
                    'message' => $e->getMessage(),
                );
            } else {
                $errorData = array(
                    'status_code' => 500,
                    'message' => 'Internal Server Error.',
                );
            }

            return new \Eole\Core\ApiResponse($errorData, $errorData['status_code']);
        });
    }
}
