<?php

namespace Eole\RestApi\ControllerProvider;

use League\OAuth2\Server\Exception\OAuthException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Alcalyn\SerializableApiResponse\ApiResponse;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;

class OAuth2ControllerProvider implements ControllerProviderInterface
{
    /**
     * @param Application $app
     */
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->post('/access-token', function (Request $request) use ($app) {
            try {
                $token = $app['sandstone.oauth.controller']->postAccessToken($request);

                return new ApiResponse($token);
            } catch (OAuthException $e) {
                throw new HttpException($e->httpStatusCode, $e->errorType.': '.$e->getMessage(), $e);
            }
        });

        return $controllers;
    }
}
