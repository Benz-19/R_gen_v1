<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendVerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $code;
    public $email;

    public function __construct(string $code, string $email)
    {
        $this->code = $code;
        $this->email = $email;
    }

    public function build()
    {
        return $this->subject('Verify Your Account - ReconAgent')
                    ->html($this->htmlTemplate());
    }

    private function htmlTemplate(): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #090d16; color: #cbd5e1; margin: 0; padding: 40px 20px; }
                .card { max-width: 480px; margin: 0 auto; background-color: #0f172a; border: 1px solid #1e293b; border-radius: 12px; padding: 32px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); }
                .logo { font-size: 20px; font-weight: bold; color: #10b981; text-align: center; margin-bottom: 24px; letter-spacing: 1px; }
                h2 { color: #f8fafc; font-size: 20px; margin-top: 0; text-align: center; }
                p { font-size: 14px; line-height: 1.6; color: #94a3b8; text-align: center; }
                .code-box { background-color: #020617; border: 1px dashed #10b981; border-radius: 8px; font-size: 32px; font-weight: bold; color: #34d399; letter-spacing: 8px; text-align: center; padding: 16px; margin: 24px 0; }
                .footer { font-size: 12px; color: #64748b; text-align: center; margin-top: 24px; border-top: 1px solid #1e293b; padding-top: 16px; }
            </style>
        </head>
        <body>
            <div class='card'>
                <div class='logo'>⚡ RECONAGENT</div>
                <h2>Verify Your Email Address</h2>
                <p>Use the 6-digit verification code below to complete your registration for <strong>{$this->email}</strong>.</p>
                <div class='code-box'>{$this->code}</div>
                <p>This code is valid for <strong>15 minutes</strong>. If you did not request this code, you can safely ignore this email.</p>
                <div class='footer'>
                    &copy; " . date('Y') . " ReconAgent Inc. All rights reserved.
                </div>
            </div>
        </body>
        </html>
        ";
    }
}