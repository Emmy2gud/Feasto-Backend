<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WalletLedgerEntryNotification extends Notification
{
    use Queueable;

    public function __construct(public $entry = null)
    {
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Wallet activity')
            ->line('There is a new activity on your wallet.');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'wallet.ledger.entry',
            'entry_id' => $this->entry?->id,
        ];
    }
}
