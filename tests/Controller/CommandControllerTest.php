<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CommandControllerTest extends WebTestCase
{
    private $client;


    protected function setUp(): void
    {
        $this->client = static::createClient();

    }

    public function getAdminToken(): string
    {
        $data = [
            'email' => 'admin@local.host',
            'password' => 'admin',
        ];
        $this->client->request('POST', '/api/login_check', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $response = json_decode($this->client->getResponse()->getContent(), true);

        return $response['token'];
    }

    public function getUserToken(): string
    {

        $data = [
            'email' => 'user@local.host',
            'password' => 'user',
        ];
        $this->client->request('POST', '/api/login_check', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $response = json_decode($this->client->getResponse()->getContent(), true);

        return $response['token'];
    }

  /* public function testNewCommand(): void
    {

            $data = [
                'number' => 123,
                'date' => '2023-11-30',
                'status' => 'En attente',
                'total_price' => 100.0,
            ];

          $token = $this->getAdminToken();
            $this->client->request('POST', '/api/command/new', [], [], [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            ], json_encode($data));

            $this->assertSame(201, $this->client->getResponse()->getStatusCode());

            $responseContent = $this->client->getResponse()->getContent();

            $response = json_decode($responseContent, true);

            $this->assertArrayHasKey('number', $response, 'La clé number doit être présente dans la réponse');
            $this->assertSame($data['number'], $response['number']);

    }
    */


    public function testUpdateCommand(): void
    {
        $token = $this->getAdminToken();

        $existingCommandId = 1;

        $updatedCommandData = [
            'number' => 456,
            'date' => '2023-12-02',
            'status' => 'Livré',
            'total_price' => 200.0,
        ];


        $this->client->request('PUT', '/api/command/update/' . $existingCommandId, [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ], json_encode($updatedCommandData));

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $updatedCommandResponse = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame($updatedCommandData['number'], $updatedCommandResponse['number']);
        $this->assertSame($updatedCommandData['status'], $updatedCommandResponse['status']);
        $this->assertSame($updatedCommandData['total_price'], $updatedCommandResponse['totalPrice']);
    }


    protected function tearDown(): void
    {

    }
}
