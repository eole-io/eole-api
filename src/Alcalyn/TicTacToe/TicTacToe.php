<?php

namespace Alcalyn\TicTacToe;

class TicTacToe
{
    /**
     * @var string
     */
    const NONE = '-';

    /**
     * @var string
     */
    const X = 'X';

    /**
     * @var string
     */
    const O = 'O';

    /**
     * @var string
     */
    private $grid;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->clearGrid();
    }

    /**
     * @return self
     */
    public function clearGrid()
    {
        $this->grid = str_repeat(self::NONE, 9);

        return $this;
    }

    /**
     * @param int $col
     * @param int $row
     * @param string $symbol
     *
     * @return self
     */
    public function set($col, $row, $symbol)
    {
        $this->checkCoords($col, $row);
        $this->checkSymbol($symbol);

        $this->grid{$this->toIndex($col, $row)} = $symbol;

        return $this;
    }

    /**
     * @param string $col
     * @param string $row
     *
     * @return string
     */
    public function get($col, $row)
    {
        $this->checkCoords($col, $row);

        return $this->grid{$this->toIndex($col, $row)};
    }

    /**
     * @param string $col
     * @param string $row
     *
     * @return bool
     */
    public function isEmpty($col, $row)
    {
        $this->checkCoords($col, $row);

        return self::NONE === $this->get($col, $row);
    }

    /**
     * @param int $col
     * @param int $row
     * @param string $symbol
     *
     * @return self
     *
     * @throws Exception\InvalidMoveException
     */
    public function play($col, $row, $symbol)
    {
        $this->checkCoords($col, $row);
        $this->checkSymbol($symbol);

        if (null !== $this->getWinner()) {
            throw new Exception\InvalidMoveException('Game already finnished.');
        }

        if (!$this->isEmpty($col, $row)) {
            throw new Exception\InvalidMoveException(sprintf(
                'Case already filled by %s.',
                $this->get($col, $row)
            ));
        }

        $this->set($col, $row, $symbol);

        return $this;
    }

    /**
     * @return string|null Possible values:
     *             'X'  => X won
     *             'O'  => O won
     *             '-'  => draw
     *             null => party not finished
     */
    public function getWinner()
    {
        $possibleRows = array(
            [0, 1, 2],
            [3, 4, 5],
            [6, 7, 8],
            [0, 3, 6],
            [1, 4, 7],
            [2, 5, 8],
            [0, 4, 8],
            [2, 4, 6],
        );

        foreach ($possibleRows as $row) {
            if ($this->isBrochette($row[0], $row[1], $row[2])) {
                return $row[0];
            }
        }

        if (false === strpos($this->grid, self::NONE)) {
            return self::NONE;
        }

        return null;
    }

    /**
     * @param int $col
     * @param int $row
     *
     * @return string
     */
    private function toIndex($col, $row)
    {
        $this->checkCoords($col, $row);

        return ($col * 3) + ($row % 3);
    }

    /**
     * @param string $symbol
     *
     * @throws Exception\UnknownSymbolException
     */
    private function checkSymbol($symbol)
    {
        if (!in_array($symbol, [self::NONE, self::X, self::O])) {
            throw new Exception\UnknownSymbolException($symbol);
        }
    }

    /**
     * @param int $col
     * @param int $row
     *
     * @throws Exception\InvalidCoordsException
     */
    private function checkCoords($col, $row)
    {
        if ($col < 0 || $col > 2) {
            throw new Exception\InvalidCoordsException('col', $col);
        }

        if ($row < 0 || $row > 2) {
            throw new Exception\InvalidCoordsException('row', $row);
        }
    }

    /**
     * @param integer $a
     * @param integer $b
     * @param integer $c
     *
     * @return bool
     */
    private function isBrochette($a, $b, $c)
    {
        return
            $this->grid[$a] !== self::NONE &&
            $this->grid[$a] === $this->grid[$b] &&
            $this->grid[$a] === $this->grid[$c] ;
    }
}
