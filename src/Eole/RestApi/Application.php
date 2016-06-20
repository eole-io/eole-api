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
        $this->mountOAuth2Controller();
        $this->loadRestApis();
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

        $this->on(\Symfony\Component\HttpKernel\KernelEvents::VIEW, function ($event) {
            $this['eole.api_response_filter']->onKernelView($event);
        });
    }

    /**
     * Mount /oauth
     */
    private function mountOAuth2Controller()
    {
        $this->mount('oauth', new ControllerProvider\OAuth2ControllerProvider());
    }

    /**
     * Mount Eole and games RestApi endpoints.
     */
    private function loadRestApis()
    {
        foreach ($this['environment']['mods'] as $modName => $modConfig) {
            $modClass = $modConfig['provider'];
            $mod = new $modClass();
            $provider = $mod->createControllerProvider();
            $prefix = 'api';

            if ($mod instanceof \Eole\Silex\GameProvider) {
                $prefix = 'api/games/'.$modName;
            }

            if ($provider instanceof \Pimple\ServiceProviderInterface) {
                $this->register($provider);
            }

            if ($provider instanceof \Silex\Api\ControllerProviderInterface) {
                $this->mount($prefix, $provider);
            }
        }
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

            return new \Alcalyn\SerializableApiResponse\ApiResponse($errorData, $errorData['status_code']);
        });
    }
}
