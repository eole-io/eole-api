<?php

namespace Eole\Core\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Alcalyn\SerializableApiResponse\ApiResponse;
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
     * @return ApiResponse
     */
    public function getGames()
    {
        $games = $this->gameRepository->findAll();

        return new ApiResponse($games);
    }

    /**
     * @param int
     *
     * @return ApiResponse
     *
     * @throw NotFoundHttpException
     */
    public function getGameById($id)
    {
        $game = $this->gameRepository->find($id);

        if (null === $game) {
            throw new NotFoundHttpException('No game with this id.');
        }

        return new ApiResponse($game);
    }

    /**
     * @param string
     *
     * @return ApiResponse
     *
     * @throw NotFoundHttpException
     */
    public function getGameByName($name)
    {
        $game = $this->gameRepository->findOneByName($name);

        if (null === $game) {
            throw new NotFoundHttpException('No game with this name.');
        }

        return new ApiResponse($game);
    }
}
