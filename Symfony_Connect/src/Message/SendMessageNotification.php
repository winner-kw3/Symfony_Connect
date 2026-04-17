<?php

namespace App\Message;

class SendMessageNotification
{
    public function __construct(
        private string $email,
        private string $sender,
        private string $content
    ) {}

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getSender(): string
    {
        return $this->sender;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}


