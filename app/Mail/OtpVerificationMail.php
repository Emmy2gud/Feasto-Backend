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

class OtpVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public $otpCode, public $otpRecord)
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Otp Verification Mail',
            from:new Address(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME') ??'Resety'),
            to: $this->otpRecord->email,
            cc: [],
            bcc: [],
        );
    }
    public function render()
    {
        $otp = $this->otpCode ;

        return "
       <div style='font-family: Arial, Helvetica, sans-serif; background-color: #f7f7f7; padding: 40px 15px;'>
    <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; border: 1px solid #eeeeee;'>

        <!-- Header -->
        <div style='background-color: #EE8C2B; padding: 28px 30px; text-align: center;'>
            <h1 style='margin: 0; color: #ffffff; font-size: 26px; font-weight: 700;'>
                Feasto
            </h1>
        </div>

        <!-- Content -->
        <div style='padding: 40px 35px;'>
            <h2 style='margin: 0 0 15px; color: #222222; font-size: 24px;'>
                Reset Your Password
            </h2>

            <p style='margin: 0 0 25px; color: #555555; font-size: 15px; line-height: 1.7;'>
                We received a request to reset the password for your account.
                Use the verification code below to continue.
            </p>

            <!-- OTP Box -->
            <div style='background-color: #FFF6ED; border: 1px solid #F8D5B1; border-radius: 12px; padding: 25px; text-align: center; margin: 30px 0;'>
                <p style='margin: 0 0 8px; color: #777777; font-size: 13px;'>
                    YOUR VERIFICATION CODE
                </p>

                <div style='font-size: 36px; font-weight: 700; letter-spacing: 8px; color: #EE8C2B;'>
                    $otp
                </div>
            </div>

            <p style='margin: 0 0 15px; color: #555555; font-size: 14px; line-height: 1.6;'>
                This code will expire in <strong>60 minutes</strong>.
                For your security, please do not share this code with anyone.
            </p>

            <p style='margin: 25px 0 0; color: #555555; font-size: 14px; line-height: 1.6;'>
                If you did not request a password reset, you can safely ignore this email.
            </p>
        </div>

        <!-- Footer -->
        <div style='border-top: 1px solid #eeeeee; padding: 22px 30px; text-align: center;'>
            <p style='margin: 0; color: #999999; font-size: 12px; line-height: 1.6;'>
                This is an automated message. Please do not reply to this email.
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
        return [];
    }
}
