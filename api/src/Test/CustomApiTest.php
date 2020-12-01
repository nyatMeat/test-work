<?php


namespace App\Test;


use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CustomApiTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    public function registerUser(Client $client, string $email, string $password)
    {
        $client->request("POST", '/register',
            ['json' => ['email' => $email, 'password' => $password]]);
    }

    public function loginUser(Client $client, string $email, string $password)
    {
        $response = $client->request('POST', '/authentication_token',
            ['json' => ['email' => $email, 'password' => $password]]);
        return json_decode($response->getContent(), true)['token'] ?? '';
    }


    public function createNewUser($email, $password)
    {
        $user = (new User())
            ->setEmail($email);
        $encoder = static::$container->get(UserPasswordEncoderInterface::class);
        $user->setPassword($encoder->encodePassword($user, $password));
        $em = $this->getManager();
        $em->persist($user);
        $em->flush();
        return $user;
    }

    public function getUserByEmail($email)
    {
        return static::$container->get(UserRepository::class)
            ->findOneBy(['email' => $email]);
    }

    /**
     * @return EntityManagerInterface
     */
    public function getManager()
    {
        return static::$container->get(EntityManagerInterface::class);
    }
}
