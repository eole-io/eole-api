<?php

namespace Eole\OAuth2\Test;

use Eole\Silex\Tests\AbstractApplicationTest;

class ApplicationTest extends AbstractApplicationTest
{
    public function testCreateAccessToken()
    {
        $client = $this->createClient();

        $client->request('POST', '/oauth/access-token', array(
            'grant_type' => 'password',
            'client_id' => 'eole-angular',
            'client_secret' => 'eole-angular-secret',
            'username' => 'existing-player',
            'password' => 'good-password',
        ));

        $this->assertTrue($client->getResponse()->isSuccessful());

        $result = json_decode($client->getResponse()->getContent());

        $this->assertObjectHasAttribute('access_token', $result);
        $this->assertNotEmpty($result->access_token);
    }

    public function testCreateAccessTokenForbiddenIfPasswordWrong()
    {
        $client = $this->createClient();

        $client->request('POST', '/oauth/access-token', array(
            'grant_type' => 'password',
            'client_id' => 'eole-angular',
            'client_secret' => 'eole-angular-secret',
            'username' => 'existing-player',
            'password' => 'wrong-password',
        ));

        $this->assertTrue($client->getResponse()->isClientError());
        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }
}
