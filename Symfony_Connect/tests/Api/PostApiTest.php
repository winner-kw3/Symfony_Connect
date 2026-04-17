<?php

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PostApiTest extends WebTestCase
{
    public function testApiReturnsPostsJson(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/posts');

        
        $this->assertResponseIsSuccessful();

        
        $this->assertJson($client->getResponse()->getContent());

        
        $this->assertStringContainsString(
            'hydra:Collection',
            $client->getResponse()->getContent()
        );
    }
}