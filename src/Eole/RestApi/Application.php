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
            return new \Alcalyn\SerializableApiResponse\ApiResponseFilter(
                $this['serializer']
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

        $this['eole.listener.event_to_socket'] = function () {
            return new EventListener\EventToSocketListener(
                $this['eole.push_server'],
                $this['eole.event_serializer'],
                $this['environment']['push_server']['enabled']
            );
        };

        $this->on(\Symfony\Component\HttpKernel\KernelEvents::VIEW, function ($event) {
            $this['eole.api_response_filter']->onKernelView($event);
        });
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
        if (!$this['environment']['push_server']['enabled']) {
            return $this;
        }

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
        $this->mount('oauth', new \Eole\OAuth2\Silex\OAuth2ControllerProvider());
    }

    /**
     * Mount a mod controller provider.
     * If the provider also implements ServiceProviderInterface, it is registered.
     *
     * @param string $modName
     * @param \Eole\Silex\Mod $mod
     *
     * @return self
     */
    private function mountMod($modName, \Eole\Silex\Mod $mod)
    {
        $controllerProvider = $mod->createControllerProvider();

        if (null === $controllerProvider) {
            return $this;
        }

        if (!$controllerProvider instanceof \Silex\Api\ControllerProviderInterface) {
            throw new \LogicException(sprintf(
                'Mod controller provider class (%s) for mod %s must implement %s.',
                get_class($controllerProvider),
                $modName,
                \Silex\Api\ControllerProviderInterface::class
            ));
        }

        if ($controllerProvider instanceof \Pimple\ServiceProviderInterface) {
            $this->register($controllerProvider);
        }

        $prefix = $this->mountPrefix($modName);

        $this->mount($prefix, $controllerProvider);

        return $this;
    }

    /**
     * Get prefix for mod.
     * Can be overrided.
     *
     * @param string $modName
     *
     * @return string
     */
    public function mountPrefix($modName)
    {
        return '/api/games/'.self::modNameToUrl($modName);
    }

    /**
     * @param string $modName
     *
     * @return string
     */
    public static function modNameToUrl($modName)
    {
        return str_replace('_', '-', $modName);
    }

    /**
     * {@InheritDoc}
     */
    public function loadMod($modName)
    {
        $mod = parent::loadMod($modName);

        $this->mountMod($modName, $mod);

        return $mod;
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
