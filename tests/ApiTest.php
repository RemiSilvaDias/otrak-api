<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiTest extends WebTestCase
{
    public function testSearch($term)
    {
        $client = self::createClient();
        $client->request('GET', '/shows/search/' . $term);

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function testAired()
    {
        $client = self::createClient();
        $client->request('GET', '/shows/aired');

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function testNextShows()
    {
        $client = self::createClient();
        $client->request('GET', '/shows/next');

        $this->assertEquals(410, $client->getResponse()->getStatusCode());
    }
}
