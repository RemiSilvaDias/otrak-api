<?php

namespace App\Tests;

// use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiTest extends WebTestCase
{
    protected function createAuthenticatedClient($email = 'admin@oc.io', $password = 'admin')
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/api/login_check',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode([
                'email' => $email,
                'password' => $password,
            ])
        );

        $data = json_decode($client->getResponse()->getContent(), true);

        $client = static::createClient();
        $client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $data['token']));

        return $client;
    }

    public function testSearch()
    {
        $client = self::createClient();
        $client->request('GET', 'http://localhost:8001/api/shows/search/game');

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function testAired()
    {
        $client = static::createClient();
        $client->request('GET', 'http://localhost:8001/api/shows/aired');

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function testLogin()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', 'http://localhost:8001/api/users/profile');

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function testNextShows()
    {
        $client = static::createClient();
        $client->request('GET', 'http://localhost:8001/api/shows/next');

        $this->assertEquals(401, $client->getResponse()->getStatusCode());

        // Test "Next shows" for connected user
        $client = $this->createAuthenticatedClient();
        $client->request('GET', 'http://localhost:8001/api/shows/next');

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function testNewFollowing()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', 'http://localhost:8001/api/users/profile');

        $userId = \json_decode($client->getResponse()->getContent())->id;

        $client->request('POST', "http://localhost:8001/api/followings/new/{$userId}/0/666/1/1");

        $this->assertTrue($client->getResponse()->isSuccessful());
    }
}
