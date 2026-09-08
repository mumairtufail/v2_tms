<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class TmsDatabaseNotification extends Notification
{
    public function __construct(
        public array $payload
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->payload;
    }
}
