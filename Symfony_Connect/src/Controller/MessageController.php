<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Repository\ConversationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Message\SendMessageNotification;

final class MessageController extends AbstractController
{
    #[Route('/messages', name: 'app_messages')]
    public function index(ConversationRepository $repo): Response
    {
        $user = $this->getUser();

        $conversations = $repo->createQueryBuilder('c')
            ->where('c.user1 = :u OR c.user2 = :u')
            ->setParameter('u', $user)
            ->getQuery()
            ->getResult();

        return $this->render('message/index.html.twig', [
            'conversations' => $conversations
        ]);
    }

    #[Route('/message/new/{username}', name: 'app_message_new')]
    public function new(
        string $username,
        UserRepository $userRepo,
        ConversationRepository $convRepo,
        EntityManagerInterface $em
    ): Response {

        $me = $this->getUser();
        $user = $userRepo->findOneBy(['username' => $username]);

        if (!$user || $user === $me) {
            throw $this->createNotFoundException();
        }

        $conversation = $convRepo->createQueryBuilder('c')
            ->where('(c.user1 = :me AND c.user2 = :user) OR (c.user1 = :user AND c.user2 = :me)')
            ->setParameter('me', $me)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$conversation) {
            $conversation = new Conversation();
            $conversation->setUser1($me);
            $conversation->setUser2($user);

            $em->persist($conversation);
            $em->flush();
        }

        return $this->redirectToRoute('app_conversation', [
            'id' => $conversation->getId()
        ]);
    }

    #[Route('/messages/{id}', name: 'app_conversation')]
    public function show(Conversation $conversation, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if ($conversation->getUser1() !== $user && $conversation->getUser2() !== $user) {
            throw $this->createAccessDeniedException();
        }

        foreach ($conversation->getMessages() as $message) {
            if ($message->getSender() !== $user) {
                $message->setIsRead(true);
            }
        }

        $em->flush();

        return $this->render('message/show.html.twig', [
            'conversation' => $conversation
        ]);
    }

    #[Route('/messages/{id}/send', name: 'app_message_send', methods: ['POST'])]
    public function send(
        Conversation $conversation,
        Request $request,
        EntityManagerInterface $em,
        MessageBusInterface $bus
    ): Response {

        $message = new Message();

        $message->setConversation($conversation);
        $message->setSender($this->getUser());
        $message->setContent($request->request->get('content'));
        $message->setCreatedAt(new \DateTimeImmutable());
        $message->setIsRead(false);

        $em->persist($message);
        $em->flush();

        $recipient = ($conversation->getUser1() === $this->getUser())
            ? $conversation->getUser2()
            : $conversation->getUser1();

        $bus->dispatch(new SendMessageNotification(
            $recipient->getEmail(),
            $this->getUser()->getUsername(),
            $message->getContent()
        ));

        return $this->redirectToRoute('app_conversation', [
            'id' => $conversation->getId()
        ]);
    }
}