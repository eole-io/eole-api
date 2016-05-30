<?php

namespace Eole\Core\Controller;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Alcalyn\SerializableApiResponse\ApiResponse;
use Alcalyn\UserApi\Controller\UserController;
use Eole\Core\Exception\AlreadyAPlayerException;

class PlayerController extends UserController
{
    /**
     * Create a new guest.
     *
     * @return ApiResponse
     */
    public function postGuest(Request $request)
    {
        $password = $request->request->get('password');

        $guest = $this->api->createGuest($password);

        return new ApiResponse($guest, Response::HTTP_CREATED);
    }

    /**
     * @param Request $request
     *
     * @return ApiResponse
     *
     * @throws HttpException if no authenticated player or already a player.
     */
    public function registerGuest(Request $request)
    {
        if (null === $this->loggedUser) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, 'Need to be logged to register guest.');
        }

        $username = $request->request->get('username');
        $password = $request->request->get('password');

        try {
            $player = $this->api->registerGuest($this->loggedUser, $username, $password);
        } catch (AlreadyAPlayerException $e) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Authenticated guest is already a player.', $e);
        }

        return new ApiResponse($player, Response::HTTP_OK);
    }
}
