<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;
use App\Repository\PostRepository;

final class ProfileController extends AbstractController
{
    #[Route('/profil/{username}', name: 'app_profile')]
    public function index(string $username, UserRepository $userRepository, PostRepository $postRepository): Response
    {
        $user = $userRepository->findOneBy(['username' => $username]);
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }
        $posts = $postRepository->findBy(['author' => $user], ['createdAt' => 'DESC']);
        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'posts' => $posts,
        ]);
    }
}
