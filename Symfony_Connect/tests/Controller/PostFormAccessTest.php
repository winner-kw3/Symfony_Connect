<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;

class PostFormAccessTest extends WebTestCase
{
    public function testUserCanAccessPostForm(): void
    {
        $client = static::createClient();

        
        $container = static::getContainer();
        $user = $container->get('doctrine')
            ->getRepository(User::class)
            ->findOneBy(['email' => 'test@gmail.com']);

        $this->assertNotNull($user, 'User test introuvable en base');

        $client->loginUser($user);

        $client->request('GET', '/post/nouveau');

        $this->assertResponseIsSuccessful();
    }
}