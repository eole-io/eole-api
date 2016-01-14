<?php

namespace Alcalyn\Awale;

use Alcalyn\Awale\Exception\AwaleException;

class Awale
{
    /**
     * @var int
     */
    const PLAYER_0 = 0;

    /**
     * @var int
     */
    const PLAYER_1 = 1;

    /**
     * @var int
     */
    const DRAW = -1;

    /**
     * @var array
     */
    private $grid;

    /**
     * @var int
     */
    private $seedsPerContainer;

    /**
     * @var int
     */
    private $currentPlayer;

    /**
     * @var array|null with player and move keys.
     */
    private $lastMove;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->grid = array();
        $this->seedsPerContainer = 3;
        $this->currentPlayer = self::PLAYER_0;
    }

    /**
     * @return array
     */
    public function getGrid()
    {
        return $this->grid;
    }

    /**
     * @param array $grid
     *
     * @return self
     */
    public function setGrid(array $grid)
    {
        $this->grid = $grid;

        return $this;
    }

    /**
     * @return int
     */
    public function getSeedsPerContainer()
    {
        return $this->seedsPerContainer;
    }

    /**
     * @param int $seedsPerContainer
     *
     * @return self
     */
    public function setSeedsPerContainer($seedsPerContainer)
    {
        $this->seedsPerContainer = $seedsPerContainer;

        return $this;
    }

    /**
     * @return int The score the player must exceed to win
     */
    public function getSeedsNeededToWin()
    {
        return $this->seedsPerContainer * 6;
    }

    /**
     * @return int
     */
    public function getCurrentPlayer()
    {
        return $this->currentPlayer;
    }

    /**
     * @param int $currentPlayer
     *
     * @return self
     */
    public function setCurrentPlayer($currentPlayer)
    {
        $this->currentPlayer = $currentPlayer;

        return $this;
    }

    /**
     * @param int $player
     *
     * @return bool
     */
    public function isPlayerTurn($player)
    {
        return $this->currentPlayer === $player;
    }

    /**
     * @return self
     */
    public function changePlayerTurn()
    {
        $this->currentPlayer = 1 - $this->currentPlayer;

        return $this;
    }

    /**
     * @return array
     */
    public function getLastMove()
    {
        return $this->lastMove;
    }

    /**
     * @param array $lastMove
     *
     * @return self
     */
    public function setLastMove(array $lastMove)
    {
        $this->lastMove = $lastMove;

        return $this;
    }

    /**
     * @return self
     */
    public function initGrid()
    {
        $this->grid = array(
            array(
                'seeds' => array_fill(0, 6, $this->seedsPerContainer),
                'attic' => 0,
            ),
            array(
                'seeds' => array_fill(0, 6, $this->seedsPerContainer),
                'attic' => 0,
            ),
        );

        return $this;
    }

    /**
     * Play a move naively.
     * Do not check player turn.
     *
     * @param int $player
     * @param int $move
     *
     * @return self
     *
     * @throws AwaleException on invalid input value.
     */
    public function move($player, $move)
    {
        $this->checkPlayer($player);
        $this->checkMove($move);

        // Take seeds in hand
        $hand = $this->grid[$player]['seeds'][$move];
        $this->grid[$player]['seeds'][$move] = 0;

        $row = $player;
        $box = $move;

        /**
         * Dispatch seeds
         */
        while ($hand > 0) {
            if (0 === $row) {
                if (0 === $box) {
                    $row = 1;
                } else {
                    $box--;
                }
            } else {
                if (5 === $box) {
                    $row = 0;
                } else {
                    $box++;
                }
            }

            // Feed box
            if (($row !== $player) || ($box !== $move)) {
                $hand--;
                $this->grid[$row]['seeds'][$box]++;
            }
        }

        /**
         * Anti starve check
         */
        if ((0 === $row && 0 === $box) || (1 === $row && 5 === $box)) {
            if (($row !== $player) && self::allSeedsVulnerable($this->grid[$row]['seeds'])) {
                return $this;
            }
        }

        /**
         * Store opponent seeds
         */
        while (($row !== $player) && in_array($this->grid[$row]['seeds'][$box], array(2, 3))) {
            // Store his seeds
            $this->grid[$player]['attic'] += $this->grid[$row]['seeds'][$box];
            $this->grid[$row]['seeds'][$box] = 0;

            // Check previous box
            if (0 === $row) {
                if (5 === $box) {
                    $row = 1;
                } else {
                    $box++;
                }
            } else {
                if (0 === $box) {
                    $row = 0;
                } else {
                    $box--;
                }
            }
        }

        return $this;
    }

    /**
     * Check whether all seeds can be eaten (2 or 3).
     *
     * @param array $seeds
     *
     * @return boolean
     */
    private static function allSeedsVulnerable(array $seeds) {
        foreach ($seeds as $seed) {
            if (2 !== $seed && 3 !== $seed) {
                return false;
            }
        }

        return true;
    }

    /**
     * Play a turn, check current player turn.
     *
     * @param int $player
     * @param int $move
     *
     * @return self
     *
     * @throws AwaleException on invalid move.
     */
    public function play($player, $move)
    {
        if (!$this->isPlayerTurn($player)) {
            throw new AwaleException('Not your turn.');
        }

        if (0 === $this->grid[$player]['seeds'][$move]) {
            throw new AwaleException('This container is empty.');
        }

        $this->checkMustFeedOpponentRule($player, $move);

        $this
            ->move($player, $move)
            ->setLastMove(array(
                'player' => $player,
                'move' => $move,
            ))
            ->changePlayerTurn()
        ;

        if (!$this->hasSeeds(1 - $this->currentPlayer) && !$this->canFeedOpponent($this->currentPlayer)) {
            $this->storeRemainingSeeds($player);
        }

        return $this;
    }

    /**
     * Check if there is seeds in row of $array
     *
     * @return bool
     */
    public function hasSeeds($player)
    {
        foreach ($this->grid[$player]['seeds'] as $box) {
            if ($box > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if $player can do a move which let the opponent play
     *
     * @param int $player
     *
     * @return bool
     */
    public function canFeedOpponent($player)
    {
        if (0 === $player) {
            for ($i = 0; $i < 6; $i++) {
                if ($this->grid[0]['seeds'][$i] > $i) {
                    return true;
                }
            }
        }

        if (1 === $player) {
            for ($i = 0; $i < 6; $i++) {
                if ($this->grid[1]['seeds'][$i] > (5 - $i)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param int $player
     * @param int $move
     *
     * @throws AwaleException if player does not feed starved opponent.
     */
    public function checkMustFeedOpponentRule($player, $move)
    {
        if (
            !$this->hasSeeds(1 - $player) &&
            $this->canFeedOpponent($player) &&
            !$this->isMoveFeedsOpponent($player, $move)
        ) {
            throw new AwaleException('You must feed your opponent.');
        }
    }

    /**
     * @param int $player
     * @param int $move
     *
     * @return bool
     */
    public function isMoveFeedsOpponent($player, $move)
    {
        if (self::PLAYER_0 === $player) {
            return $this->grid[$player]['seeds'][$move] > $move;
        } else {
            return $this->grid[$player]['seeds'][$move] > (5 - $move);
        }
    }

    /**
     * Store remaining seeds in $player attic.
     *
     * @param int $player
     *
     * @return self
     */
    public function storeRemainingSeeds($player)
    {
        for ($i = 0; $i < 6; $i++) {
            $this->grid[$player]['attic'] += $this->grid[0]['seeds'][$i];
            $this->grid[0]['seeds'][$i] = 0;

            $this->grid[$player]['attic'] += $this->grid[1]['seeds'][$i];
            $this->grid[1]['seeds'][$i] = 0;
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isGameLooping()
    {
        $seeds0 = $this->grid[self::PLAYER_0]['seeds'];
        $seeds1 = $this->grid[self::PLAYER_1]['seeds'];

        if (1 === array_sum($seeds0) && $seeds0 === array_reverse($seeds1)) {
            return true;
        }

        return false;
    }

    /**
     * Check if a player reached the seeds number to win and returns it.
     * Or return null.
     *
     * @return int|null
     */
    private function getPlayerWithMoreThanHalfSeeds()
    {
        $seedsToWin = $this->getSeedsNeededToWin();

        if ($this->grid[Awale::PLAYER_0]['attic'] > $seedsToWin) {
            return self::PLAYER_0;
        }

        if ($this->grid[Awale::PLAYER_1]['attic'] > $seedsToWin) {
            return self::PLAYER_1;
        }

        return null;
    }

    /**
     * Check if a player of the grid has won
     *
     * @return int|null null for party not ended, or one of them: self::PLAYER_0, self::PLAYER_1, self::DRAW.
     */
    public function getWinner()
    {
        if (!$this->isGameOver()) {
            return null;
        }

        $player = $this->getPlayerWithMoreThanHalfSeeds();

        if (null === $player) {
            return self::DRAW;
        } else {
            return $player;
        }
    }

    /**
     * @return bool
     */
    public function isGameOver()
    {
        if (null !== $this->getPlayerWithMoreThanHalfSeeds()) {
            return true;
        }

        if (!$this->hasSeeds(1 - $this->currentPlayer) && !$this->canFeedOpponent($this->currentPlayer)) {
            return true;
        }

        if ($this->isGameLooping()) {
            return true;
        }

        return false;
    }

    /**
     * @param int $player
     *
     * @return int
     */
    public function getScore($player)
    {
        return $this->grid[$player]['attic'];
    }

    /**
     * @param int $player
     *
     * @return self
     *
     * @throws AwaleException
     */
    public function checkPlayer($player)
    {
        if (!in_array($player, array(self::PLAYER_0, self::PLAYER_1))) {
            throw new AwaleException('Invalid player value.');
        }

        return $this;
    }

    /**
     * @param int $move
     *
     * @return self
     *
     * @throws AwaleException
     */
    public function checkMove($move)
    {
        if (!is_int($move) || $move < 0 || $move > 5) {
            throw new AwaleException('Invalid move value.');
        }

        return $this;
    }

    /**
     * @param int $seedsPerContainer
     *
     * @return self
     */
    public static function createWithSeedsPerContainer($seedsPerContainer)
    {
        $awale = new Awale();

        $awale->seedsPerContainer = $seedsPerContainer;
        $awale->initGrid();

        return $awale;
    }
}
