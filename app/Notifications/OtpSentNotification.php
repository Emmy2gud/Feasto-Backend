<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OtpSentNotification extends Notification
{
    use Queueable;

    public function __construct(public $otp = null)
    {
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your verification code')
            ->line('A verification code has been sent to your phone or email.')
            ->line('If you did not request this, ignore this message.');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'otp.sent',
            'phone_or_email' => $this->otp?->phone_or_email ?? null,
        ];
    }
}
