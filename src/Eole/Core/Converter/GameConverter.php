<?php

namespace Eole\Core\Converter;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Eole\Core\Model\Game;
use Eole\Core\Repository\GameRepository;

class GameConverter
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
     * @param int|string|null $gameNameOrId
     *
     * @return Game|null
     *
     * @throws NotFoundHttpException
     */
    public function convert($gameNameOrId = null)
    {
        if (null === $gameNameOrId) {
            return null;
        }

        if (is_numeric($gameNameOrId)) {
            $game = $this->gameRepository->find($gameNameOrId);
        } else {
            $game = $this->gameRepository->findOneBy(array(
                'name' => $gameNameOrId,
            ));
        }

        if (null === $game) {
            throw new NotFoundHttpException(sprintf('Game "%s" not found.', $gameNameOrId));
        }

        return $game;
    }
}
