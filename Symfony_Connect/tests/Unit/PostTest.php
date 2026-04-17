<?php

namespace App\Tests\Unit;

use App\Entity\Post;
use PHPUnit\Framework\TestCase;

class PostTest extends TestCase
{
    public function testPostContentIsStoredCorrectly(): void
    {
        $post = new Post();
        $post->setContent('Hello Symfony');

        $this->assertSame('Hello Symfony', $post->getContent());
    }
}