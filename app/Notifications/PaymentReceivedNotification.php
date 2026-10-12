<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceivedNotification extends Notification
{
    use Queueable;

    public function __construct(public $payment = null)
    {
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Payment received')
            ->line('We received a payment for your order.')
            ->line('Thank you.');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'payment.received',
            'payment_id' => $this->payment?->id,
        ];
    }
}
