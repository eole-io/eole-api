<?php

namespace Alcalyn\Awale;

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
     * Play a move naively
     *
     * @param int $player
     * @param int $move
     *
     * @return self
     */
    public function play($player, $move)
    {
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
