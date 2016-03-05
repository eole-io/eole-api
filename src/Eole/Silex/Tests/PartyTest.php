<?php

namespace Eole\Silex\Tests;

use Symfony\Component\HttpFoundation\Response;
use Eole\Core\Model\Party;
use Eole\Core\Model\Slot;
use Eole\Core\Service\PartyManager;

class PartyTest extends AbstractApplicationTest
{
    /**
     * @param PartyManager
     */
    private $partyManager;

    /**
     * @var Party
     */
    private $party;

    /**
     * {@InheritDoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->partyManager = new PartyManager();

        $party = new Party();

        $party
            ->setGame($this->games[0])
            ->setHost($this->player)
            ->setSlots(array(
                new Slot($party, $this->player),
                new Slot($party),
            ))
        ;

        $this->party = $party;

        $this->app['orm.em']->persist($party);
        $this->app['orm.em']->flush();
    }

    /**
     * {@InheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->app['db']->executeQuery('delete from eole_core_slot');
        $this->app['db']->executeQuery('delete from eole_core_party');
    }

    public function testGetParties()
    {
        $client = $this->createClient();

        $client->request('GET', "/api/parties");

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function testGetGameParties()
    {
        $client = $this->createClient();

        $gameName = $this->games[0]->getName();

        $client->request('GET', "/api/games/$gameName/parties");

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function testGetParty()
    {
        $client = $this->createClient();

        $gameName = $this->games[0]->getName();
        $partyId = $this->party->getId();

        $client->request('GET', "/api/games/$gameName/parties/$partyId");

        $this->assertTrue($client->getResponse()->isSuccessful());

        $retrievedParty = json_decode($client->getResponse()->getContent());

        $this->assertObjectHasAttribute('id', $retrievedParty);
        $this->assertEquals($partyId, $retrievedParty->id);
    }

    public function testGetPartyReturns404WhenPartyDoesNotExists()
    {
        $client = $this->createClient();

        $gameName = $this->games[0]->getName();

        $client->request('GET', "/api/games/$gameName/parties/978979");

        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
    }

    public function testGetPartyReturns404WhenPartyExistsButInAnotherGame()
    {
        $client = $this->createClient();

        $gameName = $this->games[1]->getName();
        $partyId = $this->party->getId();

        $client->request('GET', "/api/games/$gameName/parties/$partyId");

        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
    }

    public function testGetPartyReturns404WhenGameNotFound()
    {
        $client = $this->createClient();

        $partyId = $this->party->getId();

        $client->request('GET', "/api/games/game-inexistant/parties/$partyId");

        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
    }

    public function testCreatePartyReturnsForbiddenWhenNotLogged()
    {
        $client = $this->createClient();

        $gameName = $this->games[0]->getName();

        $client->request('POST', "/api/games/$gameName/parties");

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $client->getResponse()->getStatusCode());
    }

    public function testCreateParty()
    {
        $client = $this->createClient();

        $gameName = $this->games[0]->getName();

        $client->request('POST', "/api/games/$gameName/parties", [], [], array(
            'HTTP_AUTHORIZATION' => self::createOAuth2Token('existing-player'),
        ));

        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());

        $createdParty = json_decode($client->getResponse()->getContent());

        $this->assertObjectHasAttribute('id', $createdParty);
        $this->assertObjectHasAttribute('slots', $createdParty);
        $this->assertObjectHasAttribute('host', $createdParty);

        $this->assertEquals('existing-player', $createdParty->host->username, 'Player whose create a party is the host.');
    }

    public function testJoinPartyIsForbiddenWhenAnonymous()
    {
        $client = $this->createClient();

        $gameName = $this->games[0]->getName();
        $partyId = $this->party->getId();

        $client->request('PATCH', "/api/games/$gameName/parties/$partyId/join");
    }

    public function testJoinParty()
    {
        $client = $this->createClient();

        $gameName = $this->games[0]->getName();
        $partyId = $this->party->getId();

        $client->request('PATCH', "/api/games/$gameName/parties/$partyId/join", [], [], array(
            'HTTP_AUTHORIZATION' => self::createOAuth2Token($this->player2->getUsername()),
        ));

        $this->assertTrue($client->getResponse()->isSuccessful());

        $position = json_decode($client->getResponse()->getContent());

        $this->assertInternalType('int', $position);
        $this->assertGreaterThanOrEqual(0, $position);

        $client->request('GET', "/api/games/$gameName/parties/$partyId");

        $retrievedParty = json_decode($client->getResponse()->getContent());

        $this->assertEquals($this->player2->getUsername(), $retrievedParty->slots[1]->player->username);
    }

    public function testJoinPartyThrowHttpConflictWhenJoinedByAlreadyJoinedPlayer()
    {
        $client = $this->createClient();

        $gameName = $this->games[0]->getName();
        $partyId = $this->party->getId();

        $client->request('PATCH', "/api/games/$gameName/parties/$partyId/join", [], [], array(
            'HTTP_AUTHORIZATION' => self::createOAuth2Token('existing-player'),
        ));

        $this->assertEquals(Response::HTTP_CONFLICT, $client->getResponse()->getStatusCode());
    }
}
