<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserRegisteredNotification extends Notification
{
    use Queueable;

    public function __construct(public $user = null)
    {
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Welcome to Feasto')
            ->line('Thanks for registering.')
            ->line('If you did not create an account, please contact support.');
    }
    public function toDatabase($notifiable)
    {
        return [
            'type' => 'user.registered',
           'notifiable_id' => $this->user?->id,
            'notifiable_type' => get_class($this->user),
            'data' =>'Welcome to Feasto.',
        ];
    }
    public function toArray($notifiable)
    {
        return [
            'type' => 'user.registered',
            'user_id' => $this->user?->id,
            'fullname' => $this->user?->fullname ?? null,
        ];
    }
}
