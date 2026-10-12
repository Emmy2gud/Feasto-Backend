<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ForgotPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public $user, public $token)
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset Password Mail',
            from:new Address(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME') ??'Resety'),
            to: $this->user->email,
            cc: [],
            bcc: [],
        );
    }
    public function render()
    {
        $baseUrl = env('FRONTEND_URL', 'http://localhost:3000');
        $resetUrl = "{$baseUrl}/reset-password?token={$this->token}&email=" . urlencode($this->user->email);

        return "
<div style='font-family: Arial, Helvetica, sans-serif; background-color: #f7f7f7; padding: 40px 20px;'>
    <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 14px; overflow: hidden; border: 1px solid #eeeeee;'>

```
    <div style='background-color: #EE8C2B; padding: 28px 30px; text-align: center;'>
        <h1 style='margin: 0; color: #ffffff; font-size: 26px; font-weight: 700;'>Password Reset</h1>
        <p style='margin: 8px 0 0; color: #fff4e8; font-size: 14px;'>Secure access to your account</p>
    </div>

    <div style='padding: 35px 30px;'>
        <h2 style='margin: 0 0 15px; color: #222222; font-size: 22px;'>Reset your password</h2>

        <p style='color: #555555; line-height: 1.7; font-size: 15px; margin: 0 0 20px;'>
            We received a request to reset the password for your account. Use the verification code below to continue.
        </p>

        <div style='background-color: #fff7ef; border: 1px solid #f6d5b5; border-radius: 12px; padding: 25px; text-align: center; margin: 25px 0;'>
            <p style='margin: 0 0 8px; color: #777777; font-size: 13px;'>Your verification code</p>

            <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$resetUrl}' style='background-color: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold;'>
                        Reset Password
                    </a>
                </div>

            <p style='margin: 12px 0 0; color: #888888; font-size: 12px;'>
                This code expires in 60 minutes.
            </p>
        </div>

        <p style='color: #555555; line-height: 1.7; font-size: 14px;'>
            Enter this code in the app to reset your password. For your security, never share this code with anyone.
        </p>

        <div style='margin-top: 30px; padding: 16px; background-color: #fafafa; border-radius: 8px;'>
            <p style='margin: 0; color: #777777; font-size: 13px; line-height: 1.6;'>
                If you did not request a password reset, you can safely ignore this email. Your account remains secure.
            </p>
        </div>
    </div>

    <div style='border-top: 1px solid #eeeeee; padding: 20px 30px; text-align: center;'>
        <p style='margin: 0; color: #999999; font-size: 12px;'>
            © 2026 Your App. All rights reserved.
        </p>
    </div>

</div>
```

</div>

        ";
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'view.name',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
