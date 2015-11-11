<?php

namespace Eole\Core\Controller;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Alcalyn\UserApi\Api\ApiInterface;
use Eole\Core\Repository\GameRepository;

class GameController
{
    /**
     * @var GameRepository
     */
    private $gameRepository;

    /**
     * @param GameRepository $gameRepository
     */
    public function __construct(GameRepository $gameRepository)
    {
        $this->gameRepository = $gameRepository;
    }

    /**
     * @return JsonResponse
     */
    public function getGames()
    {
        $games = $this->gameRepository->findAll();

        return new JsonResponse($games);
    }

    /**
     * @return JsonResponse
     */
    public function getGameByName($name)
    {
        $game = $this->gameRepository->findOneBy(array(
            'name' => $name,
        ));

        return new JsonResponse($game);
    }
}
