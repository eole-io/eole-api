<?php

namespace Eole\Core\Controller;

use Eole\Core\ApiResponse;
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
     * @return ApiResponse
     */
    public function getGameByName($name)
    {
        $game = $this->gameRepository->findOneBy(array(
            'name' => $name,
        ));

        return new ApiResponse($game);
    }
}
