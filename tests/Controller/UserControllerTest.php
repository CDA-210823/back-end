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

    public function testCreateUser():void
    {
        $data = [
            'email' => 'angedehain@gmailcom',
            'password' => 'passwordAzerty1!'
        ];
        $this->client->request('POST', '/api/user/new', [], [], [], json_encode($data));
        $this->assertSame(201, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame($data['email'], $response['email']);
    }

    public function testErrorMomentCreateUser()
    {
        $data = [
            'password' => 'passwordAzerty1!',
        ];
        $this->client->request('POST', '/api/user/new', [], [], [], json_encode($data));
        $this->assertSame(400, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('La création a échoué', $response['message']);
    }

    public function testGetAll()
    {
        $this->client->request('GET', '/api/user/');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($response);
    }

    public function testShowWithIncorrectId()
    {
        $data = [
            'email' => 'angeladehai@gmailcom',
        ];
        $this->client->request('GET', '/api/user/8', [], [] , [] , json_encode($data));
        $this->assertSame(404, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('Utilisateur non trouvé', $response['message']);
    }

    public function testEditUser()
    {
        $data = [
            'email' => 'angeadehai@gmailcom',
        ];
        $this->client->request('PUT', '/api/user/11', [], [], [], json_encode($data));
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('Utilisateur mis à jour', $response['message']);
    }

    public function testDeleteUser()
    {
        $data = [
            'email' => 'angeladehai@gmailcom',
        ];
        $this->client->request('DELETE', '/api/user/11', [], [], [], json_encode($data));
        $this->assertSame(204, $this->client->getResponse()->getStatusCode());

    }
}