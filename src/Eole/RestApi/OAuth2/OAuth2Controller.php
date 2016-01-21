<?php

namespace Eole\RestApi\OAuth2;

use Symfony\Component\HttpFoundation\Request;
use League\OAuth2\Server\Exception\OAuthException;
use League\OAuth2\Server\AuthorizationServer;
use Eole\Core\ApiResponse;

class OAuth2Controller
{
    /**
     * @var AuthorizationServer
     */
    private $oauthServer;

    /**
     * @param AuthorizationServer $oauthServer
     */
    public function __construct(AuthorizationServer $oauthServer)
    {
        $this->oauthServer = $oauthServer;
    }

    /**
     * @param Request $request
     *
     * @return ApiResponse
     */
    public function postAccessToken(Request $request)
    {
        $this->oauthServer->setRequest($request);

        try {
            $token = $this->oauthServer->issueAccessToken();

            return new ApiResponse($token);
        } catch (OAuthException $e) {
            return new ApiResponse($e->errorType.' - '.$e->getMessage(), $e->httpStatusCode);
        }
    }
}
