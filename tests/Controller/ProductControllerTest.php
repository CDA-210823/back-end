<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class ProductControllerTest extends webTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testGetAll()
    {
        $this->client->request('GET', 'http://localhost:8000/api/product/', [], [], []);
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($response);
    }
}