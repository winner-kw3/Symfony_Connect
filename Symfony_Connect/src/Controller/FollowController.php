<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Entity\User;
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

        if (!$this->isCsrfTokenValid('follow' . $user->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        if (!$me->isFollowing($user)) {
            $me->follow($user);  

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

        if (!$this->isCsrfTokenValid('unfollow' . $user->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        if ($me->isFollowing($user)) {
            $me->unfollow($user);  
            $em->flush();
        }

        return $this->redirectToRoute('app_profile', ['username' => $user->getUsername()]);
    }
}