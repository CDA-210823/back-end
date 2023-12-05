<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends webTestCase
{
    private $client;


    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

   public function getAdminToken(): mixed
   {
        $data = [
            'email' => 'admin@local.host',
            'password' => 'admin',
        ];
        $this->client->request('POST', '/api/login_check', [], [],
            [
            'CONTENT_TYPE' => 'application/json',
            ],
            json_encode($data));
        $response = json_decode($this->client->getResponse()->getContent(), true);

        return $response['token'];
    }

    public function getUserToken(): mixed
    {
        $data = [
            'email' => 'userAngel@local.host',
            'password' => 'user'
        ];
        $this->client->request('POST', '/api/login_check', [], [],
            [
            'CONTENT_TYPE' => 'application/json'
            ],
            json_encode($data));
        $response = json_decode($this->client->getResponse()->getContent(), true);
        return $response['token'];
    }

    public function testCreateUser():void
    {
        $data = [
            'email' => 'angedehaint@gmail.com',
            'password' => 'passwordAzerty1!'
        ];
        $this->client->request('POST', '/api/user/new', [], [], [], json_encode($data));
        $this->assertSame(201, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame($data['email'], $response['email']);
    }

    public function testGetAll()
    {
        $adminToken = $this->getAdminToken();
        $this->assertNotNull($adminToken);

        $this->client->request('GET', '/api/user/', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $adminToken,
        ]);
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($response);
    }

    public function testShowWithIncorrectId()
    {
        $adminToken = $this->getAdminToken();
        $this->assertNotNull($adminToken);

        $this->client->request('GET', '/api/user/1', [], [] , [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $adminToken,
        ]);
        $this->assertSame(404, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('Utilisateur non trouvé', $response['message']);
    }

    public function testEditUser()
    {
        $userToken = $this->getUserToken();
        $this->assertNotNull($userToken);

        $data = [
            'email' => '0userr@local.host',
        ];
        $this->client->request('PUT', '/api/user/5', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $userToken,
            ], json_encode($data));
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('Utilisateur mis à jour', $response['message']);
    }

    public function testDeleteUser()
    {
        $userToken = $this->getUserToken();
        $this->assertNotNull($userToken);

        $data = [
            'email' => '4user@local.host',
        ];
        $this->client->request('DELETE', '/api/user/6', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $userToken,
        ], json_encode($data));
        $this->assertSame(204, $this->client->getResponse()->getStatusCode());
    }
}