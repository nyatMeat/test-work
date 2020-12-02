<?php


namespace App\Tests\Functional;


use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;

class UserTest extends ApiTestCase
{
    use ReloadDatabaseTrait;

    public function testRegister()
    {
        $client = self::createClient();
        $client->request('POST', '/register', ['json' =>
            ['email' => 'alex@mail.ru']]);
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST, 'Register successful but registration data invalid');
        $client->request('POST', '/register', ['json' =>
            ['email' => 'alex', 'password' => '1234']]);
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST, 'Register successful but email has invalid format');
        $client->request('POST', '/register', ['json' =>
            ['email' => 'alex@mail.ru', 'password' => '1234']]);
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT, 'Register is not successful');
        $client->request('POST', '/register', ['json' =>
            ['email' => 'alex@mail.ru', 'password' => '1234']]);
        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT, 'Successful registration for user with already existed email');
    }

    public function testAuth()
    {
        $email = 'alex1@mail.ru';
        $password = '12345';
        $client = self::createClient();
        $client->request('POST', '/authentication_token', ['json' =>
            ['email' => $email, 'password' => $password]]);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED, 'User has been authorized but not exist in database');
        $client->request('POST', '/register', ['json' =>
            ['email' => $email, 'password' => $password]]);
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT, 'Registration failed');
        $response = $client->request('POST', '/authentication_token', ['json' =>
            ['email' => $email, 'password' => $password]]);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK, 'Authorization failed');

        $this->assertTrue(isset(json_decode($response->getContent(), true)['token']), 'Token not found in response');
        $token = json_decode($response->getContent(), true)['token'];
        $this->assertTrue(is_string($token), 'Token is not string');
        $response = $client->request('POST', '/authentication_token', ['json' =>
            ['email' => $email, 'password' => '322342432']]);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED, 'User has been authorized but password invalid');
    }
}
