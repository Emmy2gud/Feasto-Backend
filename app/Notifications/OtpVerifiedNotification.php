<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OtpVerifiedNotification extends Notification
{
    use Queueable;

    public function __construct(public $otp )
    {
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Verification successful')
            ->line('Your account has been successfully verified.')
            ->line('If you did not perform this action contact support.');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'otp.verified',
            'phone_or_email' => $this->otp?->phone_or_email ?? null,
        ];
    }
}
