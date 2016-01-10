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

        return $this
            ->move($player, $move)
            ->setLastMove(array(
                'player' => $player,
                'move' => $move,
            ))
            ->changePlayerTurn()
        ;
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
     * Store remaining seeds when game stops by impossibility to play.
     *
     * @return self
     */
    public function storeRemainingSeeds()
    {
        for ($i = 0; $i < 6; $i++) {
            $this->grid[0]['attic'] += $this->grid[0]['seeds'][$i];
            $this->grid[0]['seeds'][$i] = 0;

            $this->grid[1]['attic'] += $this->grid[1]['seeds'][$i];
            $this->grid[1]['seeds'][$i] = 0;
        }

        return $this;
    }

    /**
     * Check if a player of the grid has won
     *
     * @return int|null null for party not ended, or one of them: self::PLAYER_0, self::PLAYER_1, self::DRAW.
     */
    public function getWinner()
    {
        $seedsToWin = $this->getSeedsNeededToWin();

        if ($this->grid[0]['attic'] > $seedsToWin) {
            return self::PLAYER_0;
        }

        if ($this->grid[1]['attic'] > $seedsToWin) {
            return self::PLAYER_1;
        }

        if (($seedsToWin === $this->grid[0]['attic']) && ($seedsToWin === $this->grid[1]['attic'])) {
            return self::DRAW;
        }

        return null;
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
