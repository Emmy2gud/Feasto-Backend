<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VendorApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(public $vendor = null)
    {
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Vendor approved')
            ->line('Your vendor account has been approved.');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'vendor.approved',
            'vendor_id' => $this->vendor?->id,
        ];
    }
}
