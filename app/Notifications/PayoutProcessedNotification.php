<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PayoutProcessedNotification extends Notification
{
    use Queueable;

    public function __construct(public $payout = null)
    {
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Payout processed')
            ->line('Your payout has been processed.')
            ->line('It may take a few business days to arrive.');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'payout.processed',
            'payout_id' => $this->payout?->id,
        ];
    }
}
