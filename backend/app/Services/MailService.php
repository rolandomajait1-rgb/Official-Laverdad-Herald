<?php

namespace App\Services;

use App\Models\User;
use App\Mail\VerificationEmail;
use App\Mail\PasswordResetEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class MailService
{
    /**
     * Send email verification email
     * 
     * @param User $user
     * @param string $token
     * @return void
     * @throws \Exception
     */
    public function sendVerificationEmail(User $user, string $token): void
    {
        try {
            $verificationUrl = $this->buildVerificationUrl($token);
            
            Mail::to($user->email)->queue(new VerificationEmail($user, $verificationUrl));
            
            Log::info('Verification email queued', [
                'user_email' => $user->email,
                'operation' => 'email_verification'
            ]);
        } catch (\Exception $e) {
            Log::error('Verification email failed', [
                'user_email' => $user->email,
                'operation' => 'email_verification',
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Send password reset email
     * 
     * @param User $user
     * @param string $token
     * @return void
     * @throws \Exception
     */
    public function sendPasswordResetEmail(User $user, string $token): void
    {
        try {
            $resetUrl = $this->buildPasswordResetUrl($token, $user->email);
            
            Mail::to($user->email)->queue(new PasswordResetEmail($user, $resetUrl));
            
            Log::info('Password reset email queued', [
                'user_email' => $user->email,
                'operation' => 'password_reset'
            ]);
        } catch (\Exception $e) {
            Log::error('Password reset email failed', [
                'user_email' => $user->email,
                'operation' => 'password_reset',
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Build verification URL
     * 
     * @param string $token
     * @return string
     */
    private function buildVerificationUrl(string $token): string
    {
        return config('app.url') . '/api/email/verify-token?token=' . $token;
    }
    
    /**
     * Build password reset URL
     * 
     * @param string $token
     * @param string $email
     * @return string
     */
    private function buildPasswordResetUrl(string $token, string $email): string
    {
        return config('app.frontend_url') . '/reset-password?token=' . $token . '&email=' . urlencode($email);
    }
}
