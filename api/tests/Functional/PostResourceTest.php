<?php


namespace App\Tests\Functional;


use App\Entity\Post;
use App\Test\CustomApiTest;

class PostResourceTest extends CustomApiTest
{

    public function testGetCollection()
    {
        $user = $this->createNewUser('alex@mail.com', '1234');
        $post = (new Post())
            ->setTitle('Title')
            ->setBody('Body')
            ->setOwner($user);
        $em = $this->getManager();
        $em->persist($post);
        $em->flush();
        $client = static::createClient();
        $response = $client->request("GET", '/api/posts');
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
