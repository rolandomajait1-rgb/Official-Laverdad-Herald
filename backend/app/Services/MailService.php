<?php

namespace App\Services;

use App\Mail\PasswordResetEmail;
use App\Mail\VerificationEmail;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MailService
{
    /**
     * Ensure SMTP mailer has required settings before attempting network calls.
     *
     * @throws \RuntimeException
     */
    private function validateMailConfiguration(): void
    {
        if (config('mail.default') !== 'smtp') {
            return;
        }

        $required = [
            'MAIL_HOST' => config('mail.mailers.smtp.host'),
            'MAIL_PORT' => config('mail.mailers.smtp.port'),
            'MAIL_USERNAME' => config('mail.mailers.smtp.username'),
            'MAIL_PASSWORD' => config('mail.mailers.smtp.password'),
            'MAIL_FROM_ADDRESS' => config('mail.from.address'),
        ];

        $missing = [];
        foreach ($required as $name => $value) {
            if ($value === null || $value === '') {
                $missing[] = $name;
            }
        }

        if ($missing !== []) {
            throw new \RuntimeException('Mail configuration incomplete: '.implode(', ', $missing));
        }
    }

    /**
     * Send email verification email
     *
     * @throws \Exception
     */
    public function sendVerificationEmail(User $user, string $token): void
    {
        try {
            $this->validateMailConfiguration();
            $verificationUrl = $this->buildVerificationUrl($token);

            Mail::to($user->email)->send(new VerificationEmail($user, $verificationUrl));

            Log::info('Verification email sent', [
                'user_email' => $user->email,
                'operation' => 'email_verification',
            ]);
        } catch (\Exception $e) {
            Log::error('Verification email failed', [
                'user_email' => $user->email,
                'operation' => 'email_verification',
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Send password reset email
     *
     * @throws \Exception
     */
    public function sendPasswordResetEmail(User $user, string $token): void
    {
        try {
            $this->validateMailConfiguration();
            $resetUrl = $this->buildPasswordResetUrl($token, $user->email);

            Mail::to($user->email)->send(new PasswordResetEmail($user, $resetUrl));

            Log::info('Password reset email sent', [
                'user_email' => $user->email,
                'operation' => 'password_reset',
            ]);
        } catch (\Exception $e) {
            Log::error('Password reset email failed', [
                'user_email' => $user->email,
                'operation' => 'password_reset',
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Build verification URL
     */
    private function buildVerificationUrl(string $token): string
    {
        return config('app.frontend_url').'/verify-email?token='.$token;
    }

    /**
     * Build password reset URL
     */
    private function buildPasswordResetUrl(string $token, string $email): string
    {
        return config('app.frontend_url').'/reset-password?token='.$token.'&email='.urlencode($email);
    }
}
