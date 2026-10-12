<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(public $product = null)
    {
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Product listed')
            ->line('Your product has been listed.')
            ->action('View product', url('/products/' . ($this->product?->id ?? '')));
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'product.created',
            'product_id' => $this->product?->id,
        ];
    }
}
