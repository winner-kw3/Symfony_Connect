<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ProfileController extends AbstractController
{
    #[Route('/profil/{username}', name: 'app_profile')]
    #[IsGranted('ROLE_USER')]
    public function index(
        string $username,
        UserRepository $userRepository,
        PostRepository $postRepository
    ): Response {

        $user = $userRepository->findOneBy(['username' => $username]);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        $currentUser = $this->getUser();

        if (!$currentUser instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $isOwner = $currentUser === $user;

        $isFollowing = !$isOwner && $currentUser->getFollowing()->contains($user);

        $posts = $postRepository->findBy(
            ['author' => $user],
            ['createdAt' => 'DESC']
        );

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'posts' => $posts,
            'isOwner' => $isOwner,
            'isFollowing' => $isFollowing,
        ]);
    }

    #[Route('/profil/{username}/follow', name: 'app_profile_follow', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function follow(
        string $username,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): Response {

        $user = $userRepository->findOneBy(['username' => $username]);
        $currentUser = $this->getUser();

        if (!$currentUser instanceof User) {
            throw $this->createAccessDeniedException();
        }

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        if ($currentUser === $user) {
            throw $this->createAccessDeniedException();
        }

        if ($currentUser->getFollowing()->contains($user)) {
            $currentUser->unfollow($user);
        } else {
            $currentUser->follow($user);
        }

        $em->flush();

        return $this->redirectToRoute('app_profile', [
            'username' => $username
        ]);
    }
}