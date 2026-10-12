<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WalletCreditedNotification extends Notification
{
    use Queueable;

    public function __construct(public $wallet = null)
    {
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Wallet credited')
            ->line('Your wallet has been credited.');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'wallet.credited',
            'wallet_id' => $this->wallet?->id,
        ];
    }
}
