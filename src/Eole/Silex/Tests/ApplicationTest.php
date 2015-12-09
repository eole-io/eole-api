<?php

namespace Eole\Silex\Tests;

use Symfony\Component\HttpFoundation\Response;
use Silex\WebTestCase;
use Eole\Core\Model\Player;
use Eole\Core\Model\Game;
use Eole\Core\Service\PlayerManager;
use Eole\RestApi\Application;

class ApplicationTest extends WebTestCase
{
    /**
     * @var PlayerManager
     */
    private $playerManager;

    /**
     * {@InheritDoc}
     */
    public function createApplication()
    {
        $app = new Application(array(
            'project.root' => __DIR__.'/../../../..',
            'env' => 'test',
            'debug' => true,
        ));

        $app['security.wsse.token_validator'] = function () {
            return new WsseTokenValidatorMock();
        };

        return $app;
    }

    /**
     * {@InheritDoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->playerManager = $this->app['eole.player_manager'];

        $this->app['db']->executeQuery('delete from eole_player');
        $this->app['db']->executeQuery('delete from eole_game');

        $player = new Player();
        $player->setUsername('existing-player');
        $this->playerManager->updatePassword($player, 'good-password');

        $game0 = new Game();
        $game0->setName('game-0');

        $game1 = new Game();
        $game1->setName('game-1');

        $this->app['orm.em']->persist($game0);
        $this->app['orm.em']->persist($game1);
        $this->app['orm.em']->persist($player);
        $this->app['orm.em']->flush();
    }

    /**
     * {@InheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->app['db']->executeQuery('delete from eole_player');
    }

    public function testRootPathIs404()
    {
        $client = $this->createClient();

        $client->request('GET', '/');
        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
    }

    public function testGetPlayersReturnsArrayOrStdClass()
    {
        $client = $this->createClient();

        $client->request('GET', '/api/players');
        $this->assertTrue($client->getResponse()->isSuccessful());

        $content = json_decode($client->getResponse()->getContent());

        $this->assertTrue(is_array($content) || is_object($content));
    }

    public function testAuthMeIsForbiddenWithoutWsse()
    {
        $client = $this->createClient();

        $client->request('GET', '/api/auth/me');

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $client->getResponse()->getStatusCode());
    }

    public function testAuthMeReturnsExpectedPlayer()
    {
        $client = $this->createClient();

        $client->request('GET', '/api/auth/me', [], [], array(
            'HTTP_X_WSSE' => $this->createWsseToken('existing-player'),
        ));

        $this->assertTrue($client->getResponse()->isSuccessful());

        $player = json_decode($client->getResponse()->getContent());

        $this->assertObjectHasAttribute('id', $player);
        $this->assertObjectHasAttribute('username', $player);
        $this->assertEquals($player->username, 'existing-player');
    }

    public function testAuthMeFailsOnInvalidCredentials()
    {
        $client = $this->createClient();

        $client->request('GET', '/api/auth/me', [], [], array(
            'HTTP_X_WSSE' => $this->createWsseToken('non-existing-player'),
        ));

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $client->getResponse()->getStatusCode());
    }

    public function testCreatePlayerReturnsCreatedStatusCodeAndValidPlayer()
    {
        $client = $this->createClient();

        $client->request('POST', '/api/players', array(
            'username' => 'test-user',
            'password' => 'test-pass',
        ));

        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());

        $player = json_decode($client->getResponse()->getContent());

        $this->assertEquals('test-user', $player->username, 'Username is the one I defined.');
        $this->assertObjectHasAttribute('password_salt', $player, 'Player has salt and is provided through API.');
        $this->assertNotEmpty($player->password_salt, 'Salt is not empty.');
        $this->assertContains('ROLE_PLAYER', $player->roles, 'Created player has role ROLE_PLAYER.');
    }

    public function testCreatePlayerWithMissingArgumentReturnsBadRequest()
    {
        $client = $this->createClient();

        $client->request('POST', '/api/players', array(
            'username' => '',
            'password' => 'test-pass',
        ));

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());

        $client->request('POST', '/api/players', array(
            'username' => 'test-user',
            'password' => '',
        ));

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
    }

    public function testCreatePlayerTwiceReturnsConflictStatusCode()
    {
        $client = $this->createClient();

        $client->request('POST', '/api/players', array(
            'username' => 'test-user',
            'password' => 'test-pass',
        ));

        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());

        $client->request('POST', '/api/players', array(
            'username' => 'test-user',
            'password' => 'test-pass',
        ));

        $this->assertEquals(Response::HTTP_CONFLICT, $client->getResponse()->getStatusCode());
    }

    public function testCreateGuestReturnsAGuest()
    {
        $client = $this->createClient();

        $client->request('POST', '/api/players/guest');

        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());

        $guest = json_decode($client->getResponse()->getContent());

        $this->assertObjectHasAttribute('id', $guest);
        $this->assertObjectHasAttribute('username', $guest);
        $this->assertObjectHasAttribute('guest', $guest);

        $this->assertTrue($guest->guest, 'Guest field is set to true.');

        $client->request('GET', '/api/players/'.$guest->username);

        $guestRetrieved = json_decode($client->getResponse()->getContent());

        $this->assertEquals($guestRetrieved, $guest);
    }

    public function testCreateGuestGenerateDifferentGuestWhenCalledMultipleTimes()
    {
        $client = $this->createClient();
        $guests = array();

        $client->request('POST', '/api/players/guest');
        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode(), 'Creating first guest.');
        $guests []= json_decode($client->getResponse()->getContent());

        $client->request('POST', '/api/players/guest');
        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode(), 'Creating second guest.');
        $guests []= json_decode($client->getResponse()->getContent());

        $this->assertNotEquals($guests[0]->id, $guests[1]->id, 'Comparing guests ids.');
        $this->assertNotEquals($guests[0]->username, $guests[1]->username, 'Comparing guests usernames.');
    }

    public function testCreateGuestUseProvidedPasswordIfAny()
    {
        $client = $this->createClient();

        $client->request('POST', '/api/players/guest', array(
            'provided-password',
        ));

        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());

        $guest = json_decode($client->getResponse()->getContent());

        $this->assertObjectHasAttribute('id', $guest);
        $this->assertObjectHasAttribute('username', $guest);
        $this->assertObjectHasAttribute('guest', $guest);

        $this->assertTrue($guest->guest, 'Guest field is set to true.');

        $client->request('GET', '/api/players/'.$guest->username);

        $guestRetrieved = json_decode($client->getResponse()->getContent());

        $this->assertEquals($guestRetrieved, $guest);

        // Todo check whether password is provided password
    }

    public function testAuthMeCanAuthenticateCreatedGuest()
    {
        $client = $this->createClient();

        $client->request('POST', '/api/players/guest');
        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode(), 'Creating first guest.');
        $guest = json_decode($client->getResponse()->getContent());

        $client->request('GET', '/api/auth/me', [], [], array(
            'HTTP_X_WSSE' => $this->createWsseToken($guest->username),
        ));

        $this->assertTrue($client->getResponse()->isSuccessful());

        $authenticatedGuest = json_decode($client->getResponse()->getContent());

        $this->assertEquals($guest->username, $authenticatedGuest->username, 'Authenticated guest is the one I created.');
    }

    public function testPlayersCountReturnsExpectedValues()
    {
        $client = $this->createClient();

        $client->request('GET', '/api/players-count');
        $this->assertTrue($client->getResponse()->isSuccessful(), 'Players count returns successful response.');

        $initialCount = json_decode($client->getResponse()->getContent());

        $client->request('POST', '/api/players/guest');
        $client->request('POST', '/api/players/guest');

        $client->request('GET', '/api/players-count');

        $reponse = json_decode($client->getResponse()->getContent());

        $this->assertTrue($client->getResponse()->isSuccessful(), 'Players count returns successful response.');
        $this->assertEquals($initialCount + 2, $reponse, 'When I create 2 new players, players count is incremented by 2.');
    }

    public function testDeletePlayer()
    {
        $client = $this->createClient();

        $client->request('DELETE', '/api/players/existing-player');
        $this->assertTrue($client->getResponse()->isSuccessful(), 'Delete user returns successful response.');

        $client->request('GET', '/api/players/existing-player');
        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode(), 'Retrieving a deleted user returns 404.');

        $client->request('DELETE', '/api/players/existing-player');
        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode(), 'Delete an inexisting user returns 404.');
    }

    public function testRegisterGuest()
    {
        $client = $this->createClient();

        $client->request('POST', '/api/players/guest');
        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());

        $guest = json_decode($client->getResponse()->getContent());

        $client->request('POST', '/api/players/register', array(
            'username' => 'Killer60',
            'password' => 'myPassword'
        ), [], array(
            'HTTP_X_WSSE' => $this->createWsseToken($guest->username),
        ));
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $player = json_decode($client->getResponse()->getContent());

        $this->assertEquals($guest->id, $player->id, 'Registered player keep the same id of the guest.');
        $this->assertFalse($player->guest, 'Registered player is no longer a guest.');
        $this->assertEquals('Killer60', $player->username, 'Player username is updated to the one requested.');
    }

    public function testRegisterGuestNeedsToBeLogged()
    {
        $client = $this->createClient();

        $client->request('POST', '/api/players/guest');

        $client->request('POST', '/api/players/register', array(
            'username' => 'Killer60',
        ));

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $client->getResponse()->getStatusCode());
    }

    public function testRegisterGuestFailsOnAlreadyPlayer()
    {
        $client = $this->createClient();

        $client->request('POST', '/api/players/register', array(
            'username' => 'Killer60',
            'password' => 'myPassword'
        ), [], array(
            'HTTP_X_WSSE' => $this->createWsseToken('existing-player'),
        ));

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
    }

    public function testGetGames()
    {
        $client = $this->createClient();

        $client->request('GET', '/api/games');

        $this->assertTrue($client->getResponse()->isSuccessful(), 'Response is successful');

        $games = json_decode($client->getResponse()->getContent());

        $this->assertCount(2, $games);

        $this->assertObjectHasAttribute('id', $games[0]);
        $this->assertObjectHasAttribute('name', $games[0]);
        $this->assertObjectHasAttribute('id', $games[1]);
        $this->assertObjectHasAttribute('name', $games[1]);
    }

    public function testGetGameByNameReturnsExpectedGame()
    {
        $client = $this->createClient();

        $client->request('GET', '/api/games/game-0');

        $this->assertTrue($client->getResponse()->isSuccessful(), 'Response is successful');

        $game = json_decode($client->getResponse()->getContent());

        $this->assertObjectHasAttribute('id', $game);
        $this->assertObjectHasAttribute('name', $game);
        $this->assertEquals('game-0', $game->name);
    }

    public function testChangePassword()
    {
        $client = $this->createClient();
    }

    /**
     * @param string $username
     *
     * @return string
     */
    private function createWsseToken($username)
    {
        return 'UsernameToken Username="'.$username.'", PasswordDigest="good-password", Nonce="nonce", Created="timestamp"';
    }
}
