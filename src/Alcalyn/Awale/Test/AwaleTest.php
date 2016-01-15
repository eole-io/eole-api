<?php

namespace Alcalyn\Awale\Test;

use Alcalyn\Awale\Exception\AwaleException;
use Alcalyn\Awale\Awale;

class AwaleTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateWith3Seeds()
    {
        $awale = Awale::createWithSeedsPerContainer(3);

        $expectedGrid = array(
            array(
                'seeds' => array(3, 3, 3, 3, 3, 3),
                'attic' => 0,
            ),
            array(
                'seeds' => array(3, 3, 3, 3, 3, 3),
                'attic' => 0,
            ),
        );

        $this->assertEquals(3, $awale->getSeedsPerContainer());
        $this->assertEquals(18, $awale->getSeedsNeededToWin());
        $this->assertEquals($expectedGrid, $awale->getGrid());
    }

    public function testCreateWith5Seeds()
    {
        $awale = Awale::createWithSeedsPerContainer(5);

        $expectedGrid = array(
            array(
                'seeds' => array(5, 5, 5, 5, 5, 5),
                'attic' => 0,
            ),
            array(
                'seeds' => array(5, 5, 5, 5, 5, 5),
                'attic' => 0,
            ),
        );

        $this->assertEquals(5, $awale->getSeedsPerContainer());
        $this->assertEquals(30, $awale->getSeedsNeededToWin());
        $this->assertEquals($expectedGrid, $awale->getGrid());
    }

    public function testMoveDispatchSameRow()
    {
        $awale = Awale::createWithSeedsPerContainer(3);

        $awale->move(0, 4);

        $expectedGrid = array(
            array(
                'seeds' => array(3, 4, 4, 4, 0, 3),
                'attic' => 0,
            ),
            array(
                'seeds' => array(3, 3, 3, 3, 3, 3),
                'attic' => 0,
            ),
        );

        $this->assertEquals($expectedGrid, $awale->getGrid());
    }

    public function testMoveDispatchSameRowPlayer1()
    {
        $awale = Awale::createWithSeedsPerContainer(3);

        $awale->move(1, 2);

        $expectedGrid = array(
            array(
                'seeds' => array(3, 3, 3, 3, 3, 3),
                'attic' => 0,
            ),
            array(
                'seeds' => array(3, 3, 0, 4, 4, 4),
                'attic' => 0,
            ),
        );

        $this->assertEquals($expectedGrid, $awale->getGrid());
    }

    public function testMoveDispatchToOtherPlayer()
    {
        $awale = Awale::createWithSeedsPerContainer(3);

        $awale->move(0, 1);

        $expectedGrid = array(
            array(
                'seeds' => array(4, 0, 3, 3, 3, 3),
                'attic' => 0,
            ),
            array(
                'seeds' => array(4, 4, 3, 3, 3, 3),
                'attic' => 0,
            ),
        );

        $this->assertEquals($expectedGrid, $awale->getGrid());
    }

    public function testEat()
    {
        $awale = Awale::createWithSeedsPerContainer(3);
        $awale->setGrid(array(
            array(
                'seeds' => array(4, 0, 6, 3, 3, 3),
                'attic' => 1,
            ),
            array(
                'seeds' => array(4, 4, 3, 1, 3, 3),
                'attic' => 2,
            ),
        ));

        $awale->move(0, 2);

        $expectedGrid = array(
            array(
                'seeds' => array(5, 1, 0, 3, 3, 3),
                'attic' => 3,
            ),
            array(
                'seeds' => array(5, 5, 4, 0, 3, 3),
                'attic' => 2,
            ),
        );

        $this->assertEquals($expectedGrid, $awale->getGrid());
    }

    public function testEatMultiple()
    {
        $awale = Awale::createWithSeedsPerContainer(3);
        $awale->setGrid(array(
            array(
                'seeds' => array(4, 0, 6, 3, 3, 3),
                'attic' => 1,
            ),
            array(
                'seeds' => array(4, 2, 1, 2, 3, 3),
                'attic' => 2,
            ),
        ));

        $awale->move(0, 2);

        $expectedGrid = array(
            array(
                'seeds' => array(5, 1, 0, 3, 3, 3),
                'attic' => 9,
            ),
            array(
                'seeds' => array(5, 0, 0, 0, 3, 3),
                'attic' => 2,
            ),
        );

        $this->assertEquals($expectedGrid, $awale->getGrid());
    }

    public function testEatMultipleAgain()
    {
        $awale = Awale::createWithSeedsPerContainer(3);
        $awale->setGrid(array(
            array(
                'seeds' => array(4, 0, 8, 3, 3, 3),
                'attic' => 0,
            ),
            array(
                'seeds' => array(4, 2, 1, 3, 2, 1),
                'attic' => 0,
            ),
        ));

        $awale->move(0, 2);

        $expectedGrid = array(
            array(
                'seeds' => array(5, 1, 0, 3, 3, 3),
                'attic' => 5,
            ),
            array(
                'seeds' => array(5, 3, 2, 4, 0, 0),
                'attic' => 0,
            ),
        );

        $this->assertEquals($expectedGrid, $awale->getGrid());
    }

    public function testEatMultipleToEnd()
    {
        $awale = Awale::createWithSeedsPerContainer(3);
        $awale->setGrid(array(
            array(
                'seeds' => array(4, 0, 6, 3, 3, 3),
                'attic' => 1,
            ),
            array(
                'seeds' => array(1, 2, 1, 2, 3, 3),
                'attic' => 2,
            ),
        ));

        $awale->move(0, 2);

        $expectedGrid = array(
            array(
                'seeds' => array(5, 1, 0, 3, 3, 3),
                'attic' => 11,
            ),
            array(
                'seeds' => array(0, 0, 0, 0, 3, 3),
                'attic' => 2,
            ),
        );

        $this->assertEquals($expectedGrid, $awale->getGrid());
    }

    public function testEatMultiplePlayer1()
    {
        $awale = Awale::createWithSeedsPerContainer(3);
        $awale->setGrid(array(
            array(
                'seeds' => array(4, 0, 6, 1, 2, 3),
                'attic' => 1,
            ),
            array(
                'seeds' => array(4, 2, 3, 5, 3, 3),
                'attic' => 2,
            ),
        ));

        $awale->move(1, 3);

        $expectedGrid = array(
            array(
                'seeds' => array(4, 0, 6, 0, 0, 4),
                'attic' => 1,
            ),
            array(
                'seeds' => array(4, 2, 3, 0, 4, 4),
                'attic' => 7,
            ),
        );

        $this->assertEquals($expectedGrid, $awale->getGrid());
    }

    public function testNotFeedStartingBox()
    {
        $awale = Awale::createWithSeedsPerContainer(3);
        $awale->setGrid(array(
            array(
                'seeds' => array(4, 0, 6, 3, 4, 3),
                'attic' => 1,
            ),
            array(
                'seeds' => array(4, 4, 3, 16, 3, 3),
                'attic' => 2,
            ),
        ));

        $awale->move(1, 3);

        $expectedGrid = array(
            array(
                'seeds' => array(5, 1, 7, 5, 6, 5),
                'attic' => 1,
            ),
            array(
                'seeds' => array(5, 5, 4, 0, 5, 5),
                'attic' => 2,
            ),
        );

        $this->assertEquals($expectedGrid, $awale->getGrid());
    }

    public function testHasSeeds()
    {
        $awale = Awale::createWithSeedsPerContainer(3);
        $awale->setGrid(array(
            array(
                'seeds' => array(0, 0, 0, 0, 0, 0),
                'attic' => 1,
            ),
            array(
                'seeds' => array(1, 1, 3, 0, 3, 2),
                'attic' => 2,
            ),
        ));

        $this->assertFalse($awale->hasSeeds(0), 'hasSeeds on empty player returns false.');
        $this->assertTrue($awale->hasSeeds(1), 'hasSeeds on NON empty player returns true.');
    }

    public function testCanFeedOpponent()
    {
        $awale = Awale::createWithSeedsPerContainer(3);
        $awale->setGrid(array(
            array(
                'seeds' => array(0, 0, 0, 0, 0, 0),
                'attic' => 1,
            ),
            array(
                'seeds' => array(2, 4, 1, 2, 1, 0),
                'attic' => 2,
            ),
        ));

        $this->assertFalse($awale->canFeedOpponent(1), 'Player 1 cannot feed opponent.');

        $awale->setGrid(array(
            array(
                'seeds' => array(0, 0, 1, 3, 1, 5),
                'attic' => 1,
            ),
            array(
                'seeds' => array(0, 0, 0, 0, 0, 0),
                'attic' => 2,
            ),
        ));

        $this->assertFalse($awale->canFeedOpponent(0), 'Player 0 cannot feed opponent.');

        $awale->setGrid(array(
            array(
                'seeds' => array(0, 0, 0, 0, 0, 0),
                'attic' => 1,
            ),
            array(
                'seeds' => array(2, 4, 5, 2, 1, 0),
                'attic' => 2,
            ),
        ));

        $this->assertTrue($awale->canFeedOpponent(1), 'Player 1 can feed opponent.');

        $awale->setGrid(array(
            array(
                'seeds' => array(1, 0, 1, 3, 1, 5),
                'attic' => 1,
            ),
            array(
                'seeds' => array(0, 0, 0, 0, 0, 0),
                'attic' => 2,
            ),
        ));

        $this->assertTrue($awale->canFeedOpponent(0), 'Player 0 can feed opponent.');
    }

    public function testStoreRemainingSeeds()
    {
        $awale = Awale::createWithSeedsPerContainer(3);
        $awale->setGrid(array(
            array(
                'seeds' => array(4, 0, 6, 3, 4, 3),
                'attic' => 1,
            ),
            array(
                'seeds' => array(4, 4, 3, 16, 3, 3),
                'attic' => 2,
            ),
        ));

        $awale->storeRemainingSeeds(Awale::PLAYER_0);

        $expectedGrid = array(
            array(
                'seeds' => array(0, 0, 0, 0, 0, 0),
                'attic' => 54,
            ),
            array(
                'seeds' => array(0, 0, 0, 0, 0, 0),
                'attic' => 2,
            ),
        );

        $this->assertEquals($expectedGrid, $awale->getGrid());
    }

    public function testGetWinner()
    {
        $awale = Awale::createWithSeedsPerContainer(3);

        // No winner
        $awale->setGrid(array(
            array(
                'seeds' => array(4, 0, 6, 3, 4, 3),
                'attic' => 17,
            ),
            array(
                'seeds' => array(4, 4, 3, 16, 3, 3),
                'attic' => 2,
            ),
        ));

        $this->assertSame(null, $awale->getWinner());

        // No winner when player has exactly half of seeds
        $awale->setGrid(array(
            array(
                'seeds' => array(4, 0, 6, 3, 4, 3),
                'attic' => 18,
            ),
            array(
                'seeds' => array(4, 4, 3, 16, 3, 3),
                'attic' => 2,
            ),
        ));

        $this->assertSame(null, $awale->getWinner());

        // Winner
        $awale->setGrid(array(
            array(
                'seeds' => array(4, 0, 6, 3, 4, 3),
                'attic' => 19,
            ),
            array(
                'seeds' => array(4, 4, 3, 16, 3, 3),
                'attic' => 2,
            ),
        ));

        $this->assertSame(Awale::PLAYER_0, $awale->getWinner());

        // With 5 seeds per container
        $awale->setSeedsPerContainer(5);

        $this->assertSame(null, $awale->getWinner());

        $awale->setGrid(array(
            array(
                'seeds' => array(4, 0, 6, 3, 4, 3),
                'attic' => 18,
            ),
            array(
                'seeds' => array(4, 4, 3, 16, 3, 3),
                'attic' => 32,
            ),
        ));

        $this->assertSame(Awale::PLAYER_1, $awale->getWinner());

        // Drawn game
        $awale->setGrid(array(
            array(
                'seeds' => array(1, 0, 0, 0, 0, 0),
                'attic' => 29,
            ),
            array(
                'seeds' => array(0, 0, 0, 0, 0, 1),
                'attic' => 29,
            ),
        ));

        $this->assertSame(Awale::DRAW, $awale->getWinner());
    }

    public function testPlayChangesPlayerTurn()
    {
        $awale = Awale::createWithSeedsPerContainer(3);

        $awale->setCurrentPlayer(Awale::PLAYER_0);

        $awale->play(Awale::PLAYER_0, 1);
        $this->assertEquals(Awale::PLAYER_1, $awale->getCurrentPlayer());
        $awale->play(Awale::PLAYER_1, 4);
        $this->assertEquals(Awale::PLAYER_0, $awale->getCurrentPlayer());
        $awale->play(Awale::PLAYER_0, 2);
        $this->assertEquals(Awale::PLAYER_1, $awale->getCurrentPlayer());
    }

    public function testPlayThrowExceptionOnPlayerTriesToPlayTwice()
    {
        $awale = Awale::createWithSeedsPerContainer(3);

        $awale->setCurrentPlayer(Awale::PLAYER_0);

        $awale->play(Awale::PLAYER_0, 1);

        $this->setExpectedException(AwaleException::class);

        $awale->play(Awale::PLAYER_0, 2);
    }

    public function testPlayCannotStarveOpponentByEatingAllSeeds()
    {
        $awale = Awale::createWithSeedsPerContainer(3);

        $awale->setCurrentPlayer(Awale::PLAYER_1);
        $awale->setGrid(array(
            array(
                'seeds' => array(2, 1, 1, 2, 2, 1),
                'attic' => 0,
            ),
            array(
                'seeds' => array(4, 4, 3, 16, 7, 1),
                'attic' => 0,
            ),
        ));

        $awale->play(Awale::PLAYER_1, 4);

        $expectedGrid = array(
            array(
                'seeds' => array(3, 2, 2, 3, 3, 2),
                'attic' => 0,
            ),
            array(
                'seeds' => array(4, 4, 3, 16, 0, 2),
                'attic' => 0,
            ),
        );

        $this->assertSame($expectedGrid, $awale->getGrid());
    }

    public function testPlayForcesToFeedOpponent()
    {
        $awale = Awale::createWithSeedsPerContainer(3);

        $awale->setCurrentPlayer(Awale::PLAYER_1);
        $awale->setGrid(array(
            array(
                'seeds' => array(0, 0, 0, 0, 0, 0),
                'attic' => 0,
            ),
            array(
                'seeds' => array(2, 0, 1, 0, 3, 0),
                'attic' => 0,
            ),
        ));

        $this->setExpectedException(AwaleException::class);
        $awale->play(Awale::PLAYER_1, 2);

        $awale->play(Awale::PLAYER_1, 4);
    }

    public function testPlayStoresRemainingSeedsWhenPlayerCannotFeedsOpponent()
    {
        $awale = Awale::createWithSeedsPerContainer(4);

        $awale->setCurrentPlayer(Awale::PLAYER_0);
        $awale->setGrid(array(
            array(
                'seeds' => array(2, 0, 0, 0, 0, 0),
                'attic' => 22,
            ),
            array(
                'seeds' => array(1, 0, 0, 1, 0, 0),
                'attic' => 22,
            ),
        ));

        $awale->play(Awale::PLAYER_0, 0);

        $expectedGrid = array(
            array(
                'seeds' => array(0, 0, 0, 0, 0, 0),
                'attic' => 22,
            ),
            array(
                'seeds' => array(0, 0, 0, 0, 0, 0),
                'attic' => 26,
            ),
        );

        $this->assertEquals($expectedGrid, $awale->getGrid());
        $this->assertTrue($awale->isGameOver(), 'Game is over.');
    }

    public function testIsGameLoopingIsFalseWhenNotLooping()
    {
        $awale = Awale::createWithSeedsPerContainer(3);

        $awale->setGrid(array(
            array(
                'seeds' => array(0, 1, 2, 0, 1, 0),
                'attic' => 20,
            ),
            array(
                'seeds' => array(0, 3, 0, 0, 1, 0),
                'attic' => 21,
            ),
        ));

        $this->assertFalse($awale->isGameLooping(), 'Game is not looping.');
    }

    public function testIsGameLooping()
    {
        $awale = Awale::createWithSeedsPerContainer(3);

        $awale->setGrid(array(
            array(
                'seeds' => array(0, 1, 0, 0, 0, 0),
                'attic' => 23,
            ),
            array(
                'seeds' => array(0, 0, 0, 0, 1, 0),
                'attic' => 23,
            ),
        ));

        $this->assertTrue($awale->isGameLooping(), 'Game is looping.');
    }

    public function testIsGameOverReturnsTrueWhenThereIsAWinner()
    {
        $awale = Awale::createWithSeedsPerContainer(4);

        $awale->setGrid(array(
            array(
                'seeds' => array(0, 1, 0, 2, 0, 0),
                'attic' => 26,
            ),
            array(
                'seeds' => array(0, 0, 0, 0, 1, 0),
                'attic' => 20,
            ),
        ));

        $this->assertTrue($awale->isGameOver(), 'Game is over because there is a winner.');
    }

    public function testIsGameOverReturnsTrueBecausePlayerCannotFeedsOpponent()
    {
        $awale = Awale::createWithSeedsPerContainer(4);

        $awale->setCurrentPlayer(Awale::PLAYER_0);
        $awale->setGrid(array(
            array(
                'seeds' => array(0, 1, 0, 2, 0, 0),
                'attic' => 21,
            ),
            array(
                'seeds' => array(0, 0, 0, 0, 0, 0),
                'attic' => 20,
            ),
        ));

        $this->assertTrue($awale->isGameOver(), 'Game is over because player cannot feeds opponent.');
    }

    public function testIsGameOverReturnsTrueBecauseGameIsLooping()
    {
        $awale = Awale::createWithSeedsPerContainer(4);

        $awale->setGrid(array(
            array(
                'seeds' => array(0, 1, 0, 0, 0, 0),
                'attic' => 23,
            ),
            array(
                'seeds' => array(0, 0, 0, 0, 1, 0),
                'attic' => 23,
            ),
        ));

        $this->assertTrue($awale->isGameOver(), 'Game is over because game is looping.');
    }
}
