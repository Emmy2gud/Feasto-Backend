<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CategoryUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(public $category = null)
    {
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Category updated')
            ->line('A category has been updated.');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'category.updated',
            'category_id' => $this->category?->id,
        ];
    }
}
