<?php

namespace App\MessageHandler;

use App\Message\SendMessageNotification;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsMessageHandler]

final class SendMessageNotificationHandler
{
    public function __construct(private MailerInterface $mailer)
    {
    }

    public function __invoke(SendMessageNotification $message): void
    {
        $email = (new Email())
            ->from('no-reply@symfoconnect.com')
            ->to($message->getEmail())
            ->subject('Nouveau message')
            ->text($message->getSender() . ' : ' . $message->getContent());

        $this->mailer->send($email);
    }
}