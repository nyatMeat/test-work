<?php


namespace App\Test;


use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CustomApiTest extends ApiTestCase
{
    use ReloadDatabaseTrait;

    public function registerUser(Client $client, string $email, string $password)
    {
        $client->request("POST", '/register', [
            'json' =>
                ['email' => $email, 'password' => $password]
        ]);
    }

    public function getLoginToken(Client $client, string $email, string $password)
    {

        $response = $client->request("POST", '/authentication_token',
            [
                'json' =>
                    ['email' => $email, 'password' => $password]
            ]);
        return json_decode($response->getContent(), true)['token'] ?? '';
    }
}
