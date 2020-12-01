<?php


namespace App\Tests\Functional;


use App\Entity\Post;
use App\Test\CustomApiTest;
use Symfony\Component\HttpFoundation\Response;

class PostResourceTest extends CustomApiTest
{

    public function testGetCollection()
    {
        $client = static::createClient();
        $this->registerUser($client,'alex@mail.com', '1234');
        $user = $this->getUserByEmail('alex@mail.com');
        $post = (new Post())
            ->setTitle('Title')
            ->setBody('Body')
            ->setOwner($user);
        $em = $this->getManager();
        $em->persist($post);
        $em->flush();

        $response = $client->request("GET", '/posts');
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        $token = $this->loginUser($client,'alex@mail.com', '1234');
        $response = $client->request("GET", '/posts', ['headers' => [
            'Authorization' => 'Bearer '.$token,
        ]]);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testCreateElement()
    {

    }

    public function testEditElement()
    {

    }

    public function testGetElement()
    {

    }
}
