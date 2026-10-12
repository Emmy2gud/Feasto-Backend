<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(public $order = null)
    {
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Order received')
            ->line('We have received your order.')
            ->action('View order', url('/orders/' . ($this->order?->id ?? '')));
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'order.created',
            'order_id' => $this->order?->id,
        ];
    }
}
