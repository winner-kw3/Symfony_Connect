<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class FeedController extends AbstractController
{
    #[Route('/feed', name: 'app_feed')]
    #[IsGranted('ROLE_USER')]
    public function index(PostRepository $postRepository): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        
        $following = $user->getFollowing();

        
        if ($following->isEmpty()) {
            return $this->render('feed/index.html.twig', [
                'posts' => [],
                'empty' => true,
            ]);
        }

        
        $posts = $postRepository->createQueryBuilder('p')
            ->where('p.author IN (:users)')
            ->setParameter('users', $following)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('feed/index.html.twig', [
            'posts' => $posts,
            'empty' => false,
        ]);
    }
}