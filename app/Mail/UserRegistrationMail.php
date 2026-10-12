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

class UserRegistrationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(  public $user)
    {

    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome Email',
            from:new Address(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME') ??'Resety'),
            to: $this->user->email,
            cc: [],
            bcc: [],
        );
    }
    public function render()
    {
        $user = $this->user;

        return "
<div style='font-family: Arial, Helvetica, sans-serif; background-color: #f7f7f7; padding: 40px 15px;'>
    <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; border: 1px solid #eeeeee;'>

        <!-- Header -->
        <div style='background-color: #EE8C2B; padding: 35px 30px; text-align: center;'>
            <h1 style='margin: 0; color: #ffffff; font-size: 28px; font-weight: 700;'>
                Welcome to Feasto🎉
            </h1>

            <p style='margin: 10px 0 0; color: #FFF4E8; font-size: 14px;'>
                We're happy to have you with us.
            </p>
        </div>

        <!-- Content -->
        <div style='padding: 40px 35px;'>
            <h2 style='margin: 0 0 18px; color: #222222; font-size: 24px;'>
                Welcome, $user->name!
            </h2>

            <p style='margin: 0 0 18px; color: #555555; font-size: 15px; line-height: 1.7;'>
                Your account has been successfully created, and you're all set to get started.
            </p>

            <p style='margin: 0 0 28px; color: #555555; font-size: 15px; line-height: 1.7;'>
                We've built Your App to make your experience simple, secure, and convenient.
                You can now sign in and start exploring everything available to you.
            </p>

            <!-- Welcome Highlight -->
            <div style='background-color: #FFF6ED; border-left: 4px solid #EE8C2B; padding: 18px 20px; margin: 25px 0; border-radius: 8px;'>
                <p style='margin: 0; color: #555555; font-size: 14px; line-height: 1.6;'>
                    <strong style='color: #EE8C2B;'>You're officially in!</strong><br>
                    Your account is ready to use. Keep your login details safe and never share your password with anyone.
                </p>
            </div>



            <p style='margin: 0; color: #777777; font-size: 13px; line-height: 1.6;'>
                If you have any questions or need help, our support team is always here to assist you.
            </p>
        </div>

        <!-- Footer -->
        <div style='border-top: 1px solid #eeeeee; padding: 22px 30px; text-align: center;'>
            <p style='margin: 0; color: #999999; font-size: 12px; line-height: 1.6;'>
                Thank you for choosing Your App.
            </p>

            <p style='margin: 8px 0 0; color: #bbbbbb; font-size: 11px;'>
                &copy; 2026 Your App. All rights reserved.
            </p>
        </div>

    </div>
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
        return [

        ];
    }
}
