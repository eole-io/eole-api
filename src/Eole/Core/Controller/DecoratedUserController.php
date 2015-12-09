<?php

namespace Eole\Core\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Alcalyn\UserApi\Controller\UserController;
use Eole\Core\ApiResponse;

class DecoratedUserController extends UserController
{
    /**
     * {@InheritDoc}
     *
     * @return ApiResponse
     */
    public function getUsers()
    {
        return new ApiResponse(parent::getUsers());
    }

    /**
     * {@InheritDoc}
     *
     * @return ApiResponse
     */
    public function getUser($username)
    {
        return new ApiResponse(parent::getUser($username));
    }

    /**
     * {@InheritDoc}
     *
     * @return ApiResponse
     */
    public function postUser(Request $request)
    {
        return new ApiResponse(parent::postUser($request), Response::HTTP_CREATED);
    }

    /**
     * {@InheritDoc}
     *
     * @return ApiResponse
     */
    public function changePassword(Request $request)
    {
        return new ApiResponse(parent::changePassword($request));
    }

    /**
     * {@InheritDoc}
     *
     * @return ApiResponse
     */
    public function verifyEmail($emailVerificationToken)
    {
        return new ApiResponse(parent::verifyEmail($emailVerificationToken));
    }

    /**
     * {@InheritDoc}
     *
     * @return ApiResponse
     */
    public function deleteUser($username)
    {
        return new ApiResponse(parent::deleteUser($username));
    }

    /**
     * {@InheritDoc}
     *
     * @return ApiResponse
     */
    public function countUsers()
    {
        return new ApiResponse(parent::countUsers());
    }

    /**
     * {@InheritDoc}
     *
     * @return ApiResponse
     */
    public function authMe()
    {
        return new ApiResponse(parent::authMe());
    }
}
