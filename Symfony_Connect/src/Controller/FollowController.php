<?php

namespace App\Controller;

use App\Entity\Follow;
use App\Entity\Notification;
use App\Entity\User;
use App\Repository\FollowRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class FollowController extends AbstractController
{
    #[Route('/follow/{username}', name: 'user_follow', methods: ['POST'])]
    public function follow(
        string $username,
        Request $request,
        UserRepository $repo,
        FollowRepository $followRepo,
        EntityManagerInterface $em
    ): Response {
        $user = $repo->findOneBy(['username' => $username]);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }

        /** @var User $me */
        $me = $this->getUser();

        // ✅ impossible de se suivre soi-même
        if ($me === $user) {
            throw $this->createAccessDeniedException('Action interdite.');
        }

        // ✅ vérifie le token CSRF
        if (!$this->isCsrfTokenValid('follow' . $user->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        // ✅ vérifie qu'on ne suit pas déjà
        $existingFollow = $followRepo->findOneBy([
            'follower' => $me,
            'followed' => $user,
        ]);

        if (!$existingFollow) {
            $follow = new Follow();
            $follow->setFollower($me);
            $follow->setFollowed($user);
            $em->persist($follow);

            // ✅ notification
            $notif = new Notification();
            $notif->setRecipient($user);
            $notif->setType('follow');
            $notif->setContent($me->getUsername() . ' vous suit maintenant.');
            $em->persist($notif);

            $em->flush();
        }

        return $this->redirectToRoute('app_profile', ['username' => $user->getUsername()]);
    }

    #[Route('/unfollow/{username}', name: 'user_unfollow', methods: ['POST'])]
    public function unfollow(
        string $username,
        Request $request,
        UserRepository $repo,
        FollowRepository $followRepo,
        EntityManagerInterface $em
    ): Response {
        $user = $repo->findOneBy(['username' => $username]);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }

        /** @var User $me */
        $me = $this->getUser();

        if ($me === $user) {
            throw $this->createAccessDeniedException('Action interdite.');
        }

        // ✅ vérifie le token CSRF
        if (!$this->isCsrfTokenValid('unfollow' . $user->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $existingFollow = $followRepo->findOneBy([
            'follower' => $me,
            'followed' => $user,
        ]);

        if ($existingFollow) {
            $em->remove($existingFollow);
            $em->flush();
        }

        return $this->redirectToRoute('app_profile', ['username' => $user->getUsername()]);
    }
}