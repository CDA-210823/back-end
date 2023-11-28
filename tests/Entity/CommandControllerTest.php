<?php

namespace App\Tests\Entity;

use App\Entity\Command;
use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class CommandControllerTest extends WebTestCase
{
    public function testNew(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $user = new User('user@gmail.com', 'user');

        $client->request('POST','/api/login_check',[],[],['CONTENT_TYPE'=> 'application/json'],
        json_encode([
            "email"=> "user@gmail.com",
            "password"=> "user"
        ]));

        $responseData = json_decode($client->getResponse()->getContent(), true);
        dd($responseData);
        $token = $responseData['token'];

        $client->request(
            'POST',
            '/api/command/new',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => 'Bearer ' . $token],
            json_encode([])
        );

        $this->assertResponseIsSuccessful();
        $this->assertJson($client->getResponse()->getContent());
    }


}
