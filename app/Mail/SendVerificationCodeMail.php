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
                    <meta name='viewport' content='width=device-width, initial-scale=1.0'>

                    <style>
                        body {
                            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
                            background-color: #090d16;
                            color: #cbd5e1;
                            margin: 0;
                            padding: 40px 20px;
                        }

                        .card {
                            max-width: 480px;
                            margin: 0 auto;
                            background-color: #0f172a;
                            border: 1px solid #1e293b;
                            border-radius: 14px;
                            padding: 36px;
                            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.35);
                        }

                        .brand-header {
                            text-align: center;
                            margin-bottom: 32px;
                            padding-bottom: 24px;
                            border-bottom: 1px solid #1e293b;
                        }

                        .brand-container {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            gap: 12px;
                        }

                        .logo-box {
                            width: 38px;
                            height: 38px;
                            background-color: #ffffff;
                            border-radius: 10px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                        }

                        .brand-name {
                            color: #ffffff;
                            font-size: 22px;
                            font-weight: 800;
                            letter-spacing: -0.5px;
                        }

                        h2 {
                            color: #f8fafc;
                            font-size: 21px;
                            font-weight: 600;
                            margin: 0 0 14px;
                            text-align: center;
                            letter-spacing: -0.3px;
                        }

                        p {
                            font-size: 14px;
                            line-height: 1.7;
                            color: #94a3b8;
                            text-align: center;
                            margin: 0;
                        }

                        .email {
                            color: #cbd5e1;
                            font-weight: 500;
                        }

                        .code-box {
                            background-color: #020617;
                            border: 1px solid #263244;
                            border-radius: 10px;
                            font-size: 30px;
                            font-weight: 650;
                            color: #f8fafc;
                            letter-spacing: 8px;
                            text-align: center;
                            padding: 18px 16px;
                            margin: 28px 0;
                        }

                        .security-note {
                            background-color: #0b1220;
                            border: 1px solid #1e293b;
                            border-radius: 8px;
                            padding: 13px 15px;
                            margin-top: 22px;
                        }

                        .security-note p {
                            font-size: 12px;
                            line-height: 1.6;
                            color: #64748b;
                        }

                        .footer {
                            font-size: 11px;
                            line-height: 1.6;
                            color: #475569;
                            text-align: center;
                            margin-top: 30px;
                            border-top: 1px solid #1e293b;
                            padding-top: 20px;
                        }
                    </style>
                </head>

                <body>

                    <div class='card'>

                        <!-- Brand Header -->
                        <div class='brand-header'>
                            <div class='brand-container'>
                                <!-- Brand Wordmark -->
                                <span class='brand-name'>ReconAgent</span>
                            </div>
                        </div>

                        <!-- Heading -->
                        <h2>Verify your email address</h2>

                        <p>
                            Welcome to ReconAgent. To complete the setup of your account,
                            please enter the verification code below.
                        </p>

                        <!-- Verification Code -->
                        <div class='code-box'>
                            {$this->code}
                        </div>

                        <p>
                            This verification code was requested for
                            <span class='email'>{$this->email}</span>.
                            The code will remain valid for <strong>15 minutes</strong>.
                        </p>

                        <!-- Security Information -->
                        <div class='security-note'>
                            <p>
                                <strong>Security notice:</strong>
                                ReconAgent will never ask you to share your verification
                                code or account password. If you did not initiate this
                                request, no action is required.
                            </p>
                        </div>

                        <!-- Footer -->
                        <div class='footer'>
                            This is an automated message from ReconAgent.<br>
                            Please do not reply to this email.
                            <br><br>
                            &copy; <span id='year'></span> ReconAgent. All rights reserved.
                        </div>

                    </div>

                </body>
                </html>
                <script>
                    document.getElementById('year').textContent = new Date().getFullYear();
                </script>
        ";
    }
}