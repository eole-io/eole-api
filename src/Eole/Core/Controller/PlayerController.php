<?php

namespace Eole\Core\Controller;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Alcalyn\UserApi\Api\ApiInterface;
use Alcalyn\UserApi\Controller\UserController;
use Eole\Core\Exception\AlreadyAPlayerException;
use Eole\Core\Service\PlayerManager;

class PlayerController extends UserController
{
    /**
     * @var PlayerManager
     */
    private $playerManager;

    /**
     * @param ApiInterface $api
     * @param PlayerManager $playerManager
     */
    public function __construct(ApiInterface $api, PlayerManager $playerManager)
    {
        parent::__construct($api);

        $this->playerManager = $playerManager;
    }

    /**
     * Create a new guest.
     *
     * @return JsonResponse
     */
    public function postGuest(Request $request)
    {
        $password = $request->request->get('password');

        $guest = $this->api->createGuest($password);

        return new JsonResponse($guest, JsonResponse::HTTP_CREATED);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
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

        return new JsonResponse($player, JsonResponse::HTTP_OK);
    }
}
