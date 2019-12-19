<?php

namespace App\Tests;

use App\Entity\Show;
use App\Entity\User;
use App\Entity\Following;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiTest extends WebTestCase
{
    // Change the url if you don't run the test with the Symfony server
    public const TEST_URL = 'http://localhost:8001/';
    private $em;

    protected function setUp()
    {
        $kernel = self::bootKernel();

        $this->em = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }
    
    /**
     * Create an authentificated client for the tests
     *
     * @param string $email
     * @param string $password
     * @return void
     */
    protected function createAuthenticatedClient($email = 'test@oc.io', $password = 'test')
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
        // print_r($data);

        $client = static::createClient();
        $client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $data['token']));

        return $client;
    }

    public function testCreateUserApi()
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => 'phpunit@oc.io']);

        if(is_null($user)) {
            $client = self::createClient();
            $client->request(
                'POST',
                self::TEST_URL . 'api/users/new',
                array(),
                array(),
                array('CONTENT_TYPE' => 'application/json'),
                json_encode([
                    'username' => 'testPhpunit',
                    'email' => 'phpunit@oc.io',
                    'password' => 'test'
                ])
            );

            $this->assertTrue($client->getResponse()->isSuccessful());
        } else {
            $this->markTestSkipped('Already existing');
        }
    }

    public function testLoginUserApi()
    {
        $client = $this->createAuthenticatedClient('phpunit@oc.io', 'test');

        $this->assertNotEmpty($client);
    }

    public function testLoginApi()
    {
        $client = $this->createAuthenticatedClient('phpunit@oc.io', 'test');
        $client->request('GET', self::TEST_URL . 'api/users/profile');

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function testSearchApi()
    {
        $client = self::createClient();
        $client->request('GET', self::TEST_URL . 'api/shows/search/game');

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function testAiredApi()
    {
        $client = static::createClient();
        $client->request('GET', self::TEST_URL . 'api/shows/aired');
        $this->assertTrue($client->getResponse()->isSuccessful());

        $client = $this->createAuthenticatedClient('phpunit@oc.io', 'test');
        $client->request('GET', self::TEST_URL . 'api/shows/next');
        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function testNextShowsApi()
    {
        $client = static::createClient();
        $client->request('GET', self::TEST_URL . 'api/shows/next');

        $this->assertEquals(401, $client->getResponse()->getStatusCode());

        // Test "Next shows" for connected user
        $client = $this->createAuthenticatedClient('phpunit@oc.io', 'test');
        $client->request('GET', self::TEST_URL . 'api/shows/next');

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function testNewFollowingApi()
    {
        $client = $this->createAuthenticatedClient('phpunit@oc.io', 'test');
        $client->request('GET', self::TEST_URL . 'api/users/profile');

        $userId = \json_decode($client->getResponse()->getContent())->id;

        $client->request('POST', self::TEST_URL . "api/followings/new/{$userId}/0/666/1/1");

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function testDeleteFollowingApi()
    {
        $client = $this->createAuthenticatedClient('phpunit@oc.io', 'test');

        $client->request('GET', self::TEST_URL . 'api/users/profile');
        $userId = \json_decode($client->getResponse()->getContent())->id;
        $user = $this->em->getRepository(User::class)->findOneBy(['id' => $userId]);
        
        $show = $this->em->getRepository(Show::class)->findOneBy(['id_tvmaze' => 666]);
        $lastShowAdded = $this->em->getRepository(Following::class)->findOneBy(['user' => $user, 'tvShow' => $show, 'season' => null, 'episode' => null]);
        
        $client->request(
            'DELETE',
            self::TEST_URL . "api/followings/{$lastShowAdded->getId()}",
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode([])
        );
        $this->assertTrue($client->getResponse()->isSuccessful());
    }
}
