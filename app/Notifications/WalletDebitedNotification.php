<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WalletDebitedNotification extends Notification
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
            ->subject('Wallet debited')
            ->line('Your wallet has been debited.');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'wallet.debited',
            'wallet_id' => $this->wallet?->id,
        ];
    }
}
