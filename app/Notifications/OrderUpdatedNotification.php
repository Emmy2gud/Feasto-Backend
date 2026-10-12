<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderUpdatedNotification extends Notification
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
            ->subject('Order updated')
            ->line('Your order status has changed.')
            ->action('View order', url('/orders/' . ($this->order?->id ?? '')));
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'order.updated',
            'order_id' => $this->order?->id,
        ];
    }
}
