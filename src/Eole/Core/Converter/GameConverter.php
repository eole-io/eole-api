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
     * @param int|null $gameName
     *
     * @return Game|null
     *
     * @throws NotFoundHttpException
     */
    public function convert($gameName = null)
    {
        if (null === $gameName) {
            return null;
        }

        $game = $this->gameRepository->findOneBy(array(
            'name' => $gameName,
        ));

        if (null === $game) {
            throw new NotFoundHttpException(sprintf('Game name "%s" not found.', $gameName));
        }

        return $game;
    }
}
