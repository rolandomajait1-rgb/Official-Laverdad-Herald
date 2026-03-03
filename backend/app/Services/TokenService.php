<?php

namespace App\Services;

use App\Models\User;
use App\Models\VerificationToken;
use App\Models\PasswordResetToken;
use Illuminate\Support\Facades\Hash;

class TokenService
{
    /**
     * Generate a random 32-byte hexadecimal token
     */
    public function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Create a verification token for a user
     * 
     * @param User $user
     * @return VerificationToken
     */
    public function createVerificationToken(User $user): VerificationToken
    {
        $token = $this->generateToken();
        
        return VerificationToken::create([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => now()->addHours(24),
        ]);
    }
    
    /**
     * Validate a verification token and return the token model if valid
     * 
     * @param string $token
     * @return VerificationToken|null
     */
    public function validateVerificationToken(string $token): ?VerificationToken
    {
        return VerificationToken::valid()
            ->where('token', $token)
            ->first();
    }
    
    /**
     * Delete all verification tokens for a user
     * 
     * @param User $user
     * @return void
     */
    public function deleteUserVerificationTokens(User $user): void
    {
        VerificationToken::where('user_id', $user->id)->delete();
    }
    
    /**
     * Create a password reset token for an email
     * 
     * @param string $email
     * @return array ['token' => string, 'hashedToken' => string]
     */
    public function createPasswordResetToken(string $email): array
    {
        $token = \Illuminate\Support\Str::random(64);
        $hashedToken = hash_hmac('sha256', $token, config('app.key'));
        
        PasswordResetToken::updateOrCreate(
            ['email' => $email],
            [
                'email' => $email,
                'token' => $hashedToken,
                'created_at' => now()
            ]
        );
        
        return [
            'token' => $token,
            'hashedToken' => $hashedToken
        ];
    }
    
    /**
     * Validate a password reset token
     * 
     * @param string $email
     * @param string $token
     * @return PasswordResetToken|null
     */
    public function validatePasswordResetToken(string $email, string $token): ?PasswordResetToken
    {
        $resetRecord = PasswordResetToken::where('email', $email)->first();
        
        if (!$resetRecord) {
            return null;
        }
        
        if ($resetRecord->isExpired()) {
            $this->deletePasswordResetToken($email);
            return null;
        }
        
        $hashedToken = hash_hmac('sha256', $token, config('app.key'));
        if (!hash_equals($resetRecord->token, $hashedToken)) {
            return null;
        }
        
        return $resetRecord;
    }
    
    /**
     * Delete password reset token for an email
     * 
     * @param string $email
     * @return void
     */
    public function deletePasswordResetToken(string $email): void
    {
        PasswordResetToken::where('email', $email)->delete();
    }
}
