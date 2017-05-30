<?php

namespace Tests\Eole\Core\Service;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Eole\Core\Model\Game;
use Eole\Core\Model\Player;
use Eole\Core\Model\Party;
use Eole\Core\Model\Slot;
use Eole\Core\Service\PartyManager;

class PartyManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PartyManager
     */
    private $partyManager;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcherMock;

    /**
     * {@InheritDoc}
     */
    public function setUp()
    {
        $this->dispatcherMock = $this->getMock(EventDispatcherInterface::class);
        $this->partyManager = new PartyManager($this->dispatcherMock);
    }

    public function testCreateParty()
    {
        $game = new Game();
        $player = new Player();

        $createdParty = $this->partyManager->createParty($game, $player);

        $this->assertSame($game, $createdParty->getGame());
        $this->assertSame($player, $createdParty->getHost());
        $this->assertEquals(Party::PREPARATION, $createdParty->getState());
    }

    public function testCreatePartyCreateStandardSlots()
    {
        $game = new Game();
        $player = new Player();

        $createdParty = $this->partyManager->createParty($game, $player);

        $this->assertCount(2, $createdParty->getSlots());
        $this->assertSame($player, $createdParty->getSlot(0)->getPlayer());
        $this->assertTrue($createdParty->getSlot(1)->isFree(), 'createParty create a second empty slot.');
        $this->assertSame($createdParty, $createdParty->getSlot(0)->getParty());
        $this->assertSame($createdParty, $createdParty->getSlot(1)->getParty());
    }

    public function testGetPlayerPosition()
    {
        $party = new Party();
        $player = (new Player())->setId(1);
        $otherPlayer = (new Player())->setId(2);

        $party
            ->addSlot(new Slot($party, $player))
            ->addSlot(new Slot($party, null))
            ->addSlot(new Slot($party, $otherPlayer))
        ;

        $this->assertEquals(0, $this->partyManager->getPlayerPosition($party, $player));
        $this->assertEquals(2, $this->partyManager->getPlayerPosition($party, $otherPlayer));

        $otherParty = new Party();

        $otherParty
            ->addSlot(new Slot($otherParty, null))
            ->addSlot(new Slot($otherParty, $otherPlayer))
            ->addSlot(new Slot($otherParty, $player))
        ;

        $this->assertEquals(2, $this->partyManager->getPlayerPosition($otherParty, $player));
        $this->assertEquals(1, $this->partyManager->getPlayerPosition($otherParty, $otherPlayer));
    }

    public function testGetPlayerPositionReturnsNullWhenPlayerNotHere()
    {
        $party = new Party();
        $player = (new Player())->setId(1);
        $otherPlayer = (new Player())->setId(2);

        $party
            ->addSlot(new Slot($party, null))
            ->addSlot(new Slot($party, $otherPlayer))
        ;

        $this->assertNull($this->partyManager->getPlayerPosition($party, $player));
    }

    public function testHasPlayer()
    {
        $party = new Party();
        $player = (new Player())->setId(1);
        $otherPlayer = (new Player())->setId(2);

        $party
            ->addSlot(new Slot($party, $player))
            ->addSlot(new Slot($party, null))
            ->addSlot(new Slot($party, $otherPlayer))
        ;

        $this->assertTrue($this->partyManager->hasPlayer($party, $player));
        $this->assertTrue($this->partyManager->hasPlayer($party, $otherPlayer));

        $otherParty = new Party();

        $otherParty
            ->addSlot(new Slot($otherParty, null))
            ->addSlot(new Slot($otherParty, $player))
        ;

        $this->assertTrue($this->partyManager->hasPlayer($otherParty, $player));
        $this->assertFalse($this->partyManager->hasPlayer($otherParty, $otherPlayer));
    }

    public function testGetFreeSlotsCount()
    {
        $party = new Party();
        $player = new Player();
        $otherPlayer = new Player();

        $party
            ->addSlot(new Slot($party, $player))
            ->addSlot(new Slot($party, null))
            ->addSlot(new Slot($party, $otherPlayer))
        ;

        $this->assertEquals(1, $this->partyManager->getFreeSlotsCount($party));

        $otherParty = new Party();

        $otherParty
            ->addSlot(new Slot($otherParty, null))
            ->addSlot(new Slot($otherParty, null))
            ->addSlot(new Slot($otherParty, $player))
            ->addSlot(new Slot($otherParty, null))
        ;

        $this->assertEquals(3, $this->partyManager->getFreeSlotsCount($otherParty));
    }

    public function testHasFreeSlot()
    {
        $party = new Party();
        $player = new Player();
        $otherPlayer = new Player();

        $party
            ->addSlot(new Slot($party, $player))
            ->addSlot(new Slot($party, null))
            ->addSlot(new Slot($party, $otherPlayer))
        ;

        $this->assertTrue($this->partyManager->hasFreeSlot($party));

        $otherParty = new Party();

        $otherParty
            ->addSlot(new Slot($otherParty, $player))
            ->addSlot(new Slot($party, $otherPlayer))
        ;

        $this->assertFalse($this->partyManager->hasFreeSlot($otherParty));
    }

    public function testAddPlayer()
    {
        $party = new Party();
        $player = new Player();
        $otherPlayer = new Player();

        $party
            ->addSlot(new Slot($party, null))
            ->addSlot(new Slot($party, null))
            ->addSlot(new Slot($party, null))
        ;

        $playerPosition = $this->partyManager->addPlayer($party, $player);

        $this->assertEquals(0, $playerPosition);
        $this->assertSame($player, $party->getSlot(0)->getPlayer());

        $otherPlayerPosition = $this->partyManager->addPlayer($party, $otherPlayer);

        $this->assertEquals(1, $otherPlayerPosition);
        $this->assertSame($otherPlayer, $party->getSlot(1)->getPlayer());
    }

    public function testAddPlayerThrowsExceptionWhenFull()
    {
        $party = new Party();
        $player = new Player();
        $otherPlayer = new Player();

        $party
            ->addSlot(new Slot($party, $player))
        ;

        $this->setExpectedException('OverflowException');
        $this->partyManager->addPlayer($party, $otherPlayer);
    }

    public function testStartParty()
    {
        $party = new Party();

        $party->setState(Party::PREPARATION);

        $this->partyManager->startParty($party);

        $this->assertEquals(Party::ACTIVE, $party->getState());

        $this->setExpectedException('RuntimeException');
        $this->partyManager->startParty($party);
    }

    public function testEndParty()
    {
        $party = new Party();

        $party->setState(Party::ACTIVE);

        $this->partyManager->endParty($party);

        $this->assertEquals(Party::ENDED, $party->getState());

        $this->setExpectedException('RuntimeException');
        $this->partyManager->endParty($party);
    }
}
