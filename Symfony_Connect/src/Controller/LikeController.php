<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class LikeController extends AbstractController
{
    #[Route('/post/{id}/like', name: 'post_like', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function like(Post $post, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        
        if ($post->isLikedBy($user)) {
            $post->unlike($user);
        } else {
            $post->like($user);
        }

        $em->flush();

        return $this->redirectToRoute('app_home'); 
    }
}