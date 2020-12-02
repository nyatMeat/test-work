<?php


namespace App\Tests\Functional;


use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Post;
use App\Test\CustomApiTest;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use joshtronic\LoremIpsum;
use Symfony\Component\HttpFoundation\Response;

class PostResourceTest extends CustomApiTest
{

    public function testGetCollection()
    {
        $lorem = new LoremIpsum();
        $client = static::createClient();
        $email = 'alex22@mail.ru';
        $password = 'lsdfsfgdfgd';
        $this->registerUser($client, $email, $password);
        $token = $this->getLoginToken($client, $email, $password);
        $client->request("POST", '/posts', ['json' => ['title' => $lorem->words(5), 'body' => $lorem->words(30)]]);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED, 'Unauthorized user cannot create a post');
        $client->request("POST", '/posts', ['json' => ['title' => $lorem->words(5), 'body' => $lorem->words(30)],
            'headers' => ['Authorization' => "Bearer $token"]]);
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED, 'Post was not created');
        $client->request("POST", '/posts', ['json' => ['title' => $lorem->words(1), 'body' => $lorem->words(1)],
            'headers' => ['Authorization' => "Bearer $token"]]);
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST, 'Post created with invalid data');
        $client->request("POST", '/posts', ['json' => ['title' => $lorem->words(5), 'body' => $lorem->words(30)],
            'headers' => ['Authorization' => "Bearer $token"]]);
        $response = $client->request("GET", '/posts', ['headers' => ['Authorization' => "Bearer $token", "Accept" => "application/json"]]);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK, 'Cannot get list of posts');
        $this->assertJson($response->getContent(), 'Response is not valid json');
        $this->assertCount(2, json_decode($response->getContent(), true), 'Invalid count of posts');
        $email = 'alex223@mail.ru';
        $password = 'lsdfsfgdfgd';
        $this->registerUser($client, $email, $password);
        $token = $this->getLoginToken($client, $email, $password);
        $response = $client->request("GET", '/posts', ['headers' => ['Authorization' => "Bearer $token", "Accept" => "application/json"]]);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK, 'Cannot get list of posts by user is not owner of posts');
        $this->assertJson($response->getContent(), 'Response is not valid json');
        $this->assertCount(2, json_decode($response->getContent(), true), 'Invalid count of posts for user which is not owner');

    }

    public function testCreateElement()
    {
        $lorem = new LoremIpsum();
        $client = static::createClient();
        $email = 'alex22@mail.ru';
        $password = 'lsdfsfgdfgd';
        $this->registerUser($client, $email, $password);
        $token = $this->getLoginToken($client, $email, $password);
        $client->request("POST", '/posts', ['json' => ['title' => $lorem->words(5), 'body' => $lorem->words(30)]]);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED, 'Unauthorized user cannot create a post');
        $client->request("POST", '/posts', ['json' => ['title' => $lorem->words(5), 'body' => $lorem->words(30)],
            'headers' => ['Authorization' => "Bearer $token"]]);
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED, 'Post was not created');
        $client->request("POST", '/posts', ['json' => ['title' => $lorem->words(1), 'body' => $lorem->words(1)],
            'headers' => ['Authorization' => "Bearer $token"]]);
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST, 'Post created with invalid data');
    }

    public function testEditElement()
    {
        $lorem = new LoremIpsum();
        $client = static::createClient();
        $email = 'alex22@mail.ru';
        $password = 'lsdfsfgdfgd';
        $this->registerUser($client, $email, $password);
        $token = $this->getLoginToken($client, $email, $password);
        $response = $client->request("POST", '/posts', ['json' => ['title' => $lorem->words(5), 'body' => $lorem->words(30)],
            'headers' => ['Authorization' => "Bearer $token"]]);
        $id = json_decode($response->getContent(), true)['id'] ?? null;
        $this->assertNotNull($id, 'Id is not present in response');
        $client->request("PUT", "/posts/$id", ['json' => ['title' => $lorem->words(6), 'body' => $lorem->words(35)],
            'headers' => ['Authorization' => "Bearer $token"]]);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK, 'Post was not updated by owner');

        $email = 'alex225@mail.ru';
        $password = 'lsdfsfgdfgd';
        $this->registerUser($client, $email, $password);
        $token = $this->getLoginToken($client, $email, $password);
        $client->request("PUT", "/posts/$id", ['json' => ['title' => $lorem->words(8), 'body' => $lorem->words(37)],
            'headers' => ['Authorization' => "Bearer $token"]]);
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN, 'Post was updated by user which is not the owner of post');
    }

    public function testGetElement()
    {
        $lorem = new LoremIpsum();
        $client = static::createClient();
        $email = 'alex22@mail.ru';
        $password = 'lsdfsfgdfgd';
        $this->registerUser($client, $email, $password);
        $token = $this->getLoginToken($client, $email, $password);
        $response = $client->request("POST", '/posts', ['json' => ['title' => $lorem->words(5), 'body' => $lorem->words(30)],
            'headers' => ['Authorization' => "Bearer $token"]]);
        $id = json_decode($response->getContent(), true)['id'] ?? null;
        $this->assertNotNull($id, 'Id is not present in response');
        $client->request("GET", "/posts/$id", ['headers' => ['Authorization' => "Bearer $token"]]);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK, 'Existed item not found');
        $invalidId ='safds';
        $client->request("GET", "/posts/$invalidId", ['headers' => ['Authorization' => "Bearer $token"]]);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND,'Item with invalid id has been found');

        $email = 'alex223@mail.ru';
        $password = 'lsdfsfgdfgd';
        $this->registerUser($client, $email, $password);
        $token = $this->getLoginToken($client, $email, $password);
        $client->request("GET", "/posts/$id", ['headers' => ['Authorization' => "Bearer $token"]]);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK,'Item not found for not owner user');
    }


    public function testDeleteElement()
    {
        $lorem = new LoremIpsum();
        $client = static::createClient();
        $email = 'alex22@mail.ru';
        $password = 'lsdfsfgdfgd';
        $this->registerUser($client, $email, $password);
        $token = $this->getLoginToken($client, $email, $password);
        $response = $client->request("POST", '/posts', ['json' => ['title' => $lorem->words(5), 'body' => $lorem->words(30)],
            'headers' => ['Authorization' => "Bearer $token"]]);
        $id = json_decode($response->getContent(), true)['id'] ?? null;
        $this->assertNotNull($id, 'Id is not present in response');
        $client->request("DELETE", "/posts/$id", ['headers' => ['Authorization' => "Bearer $token"]]);
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT, 'Post was not deleted by owner');
        $client->request("DELETE", "/posts/$id", ['headers' => ['Authorization' => "Bearer $token"]]);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND, 'Post was deleted but still exist');
        $response = $client->request("POST", '/posts', ['json' => ['title' => $lorem->words(5), 'body' => $lorem->words(30)],
            'headers' => ['Authorization' => "Bearer $token"]]);

        $email = 'alex224@mail.ru';
        $password = 'lsdfsfgdfgd';
        $newPostId = json_decode($response->getContent(), true)['id'] ?? null;
        $this->assertNotNull($newPostId, 'Id is not present in response');
        $this->registerUser($client, $email, $password);
        $newUserToken = $this->getLoginToken($client, $email, $password);
        $client->request("DELETE", "/posts/$newPostId", ['headers' => ['Authorization' => "Bearer $newUserToken"]]);
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN, 'Post was deleted by user who is not the owner');
    }
}
