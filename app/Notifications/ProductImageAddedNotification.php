<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductImageAddedNotification extends Notification
{
    use Queueable;

    public function __construct(public $image = null)
    {
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Product image uploaded')
            ->line('A new image was added to your product.');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'product.image.added',
            'image_id' => $this->image?->id,
        ];
    }
}
