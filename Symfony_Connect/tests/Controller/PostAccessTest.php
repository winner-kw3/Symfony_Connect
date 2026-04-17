<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PostAccessTest extends WebTestCase
{
    public function testGuestIsRedirectedToLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/post/nouveau');

        $this->assertResponseRedirects();

        $this->assertTrue(
            str_contains($client->getResponse()->headers->get('Location'), '/login')
        );
    }
}