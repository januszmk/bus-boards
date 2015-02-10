<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function busStops()
    {
        $client = static::createClient();

        $client->request('GET', '/getBusStops');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals('application/json', $client->getResponse()->headers->get("content-type"));
    }

    public function testBusStopEntries()
    {
        $client = static::createClient();

        $client->request('GET', '/getEntries');

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $client->request('GET', '/getEntries?bus_stop=AGH');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals('application/json', $client->getResponse()->headers->get("content-type"));

        $content = $client->getResponse()->getContent();
        $this->assertTrue(preg_match("/entries/", $content) == 1);


    }
}
