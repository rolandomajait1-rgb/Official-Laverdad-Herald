<?php

namespace App\Services;

use App\Models\Article;
use App\Models\ArticleInteraction;
use App\Models\Author;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthService
{
    private TokenService $tokenService;

    private MailService $mailService;

    public function __construct(TokenService $tokenService, MailService $mailService)
    {
        $this->tokenService = $tokenService;
        $this->mailService = $mailService;
    }

    /**
     * Register a new user with email verification
     *
     * @param  array  $data  ['name', 'email', 'password']
     *
     * @throws \Exception
     */
    public function createUserWithVerification(array $data): User
    {
        try {
            // Create user
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'user',
            ]);

            // Generate verification token
            $verificationToken = $this->tokenService->createVerificationToken($user);

            // Send verification email (don't fail registration if email fails)
            try {
                $this->mailService->sendVerificationEmail($user, $verificationToken->token);
            } catch (\Exception $e) {
                Log::error('Verification email failed during registration', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'error' => $e->getMessage(),
                ]);
                // Continue - user is created, they can resend verification
            }

            return $user;
        } catch (\Exception $e) {
            Log::error('User registration failed', [
                'email' => $data['email'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Verify user email using token
     *
     * @return array ['success' => bool, 'message' => string, 'user' => User|null]
     */
    public function verifyUserEmail(string $token): array
    {
        if (! $token) {
            return [
                'success' => false,
                'message' => 'invalid_token',
                'user' => null,
            ];
        }

        $verification = $this->tokenService->validateVerificationToken($token);

        if (! $verification) {
            return [
                'success' => false,
                'message' => 'invalid_or_expired_token',
                'user' => null,
            ];
        }

        $user = User::find($verification->user_id);

        if (! $user) {
            return [
                'success' => false,
                'message' => 'user_not_found',
                'user' => null,
            ];
        }

        if ($user->hasVerifiedEmail()) {
            $this->tokenService->deleteUserVerificationTokens($user);

            return [
                'success' => true,
                'message' => 'already_verified',
                'user' => $user,
            ];
        }

        $user->markEmailAsVerified();
        $this->tokenService->deleteUserVerificationTokens($user);

        return [
            'success' => true,
            'message' => 'verified',
            'user' => $user,
        ];
    }

    /**
     * Resend verification email
     *
     * @return array ['success' => bool, 'message' => string]
     */
    public function resendVerification(string $email): array
    {
        $user = User::where('email', $email)->first();

        if ($user && ! $user->hasVerifiedEmail()) {
            try {
                // Delete old tokens
                $this->tokenService->deleteUserVerificationTokens($user);

                // Create new token
                $verificationToken = $this->tokenService->createVerificationToken($user);

                // Send email
                $this->mailService->sendVerificationEmail($user, $verificationToken->token);
            } catch (\Exception $e) {
                Log::error('Resend verification email failed', [
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Always return success to prevent user enumeration
        return [
            'success' => true,
            'message' => 'If an unverified account exists for that email, a verification email has been sent.',
        ];
    }

    /**
     * Initiate password reset process
     *
     * @return array ['success' => bool, 'message' => string]
     */
    public function initiatePasswordReset(string $email): array
    {
        $user = User::where('email', $email)->first();

        if ($user) {
            try {
                $tokenData = $this->tokenService->createPasswordResetToken($email);
                $this->mailService->sendPasswordResetEmail($user, $tokenData['token']);
            } catch (\Exception $e) {
                Log::error('Password reset initiation failed', [
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Always return success to prevent user enumeration
        return [
            'success' => true,
            'message' => 'If an account exists for that email, a password reset link has been sent.',
        ];
    }

    /**
     * Reset user password
     *
     * @return array ['success' => bool, 'message' => string]
     */
    public function resetPassword(string $email, string $token, string $newPassword): array
    {
        $resetRecord = $this->tokenService->validatePasswordResetToken($email, $token);

        if (! $resetRecord) {
            return [
                'success' => false,
                'message' => 'Invalid or expired reset token',
            ];
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            return [
                'success' => false,
                'message' => 'User not found',
            ];
        }

        try {
            $user->password = Hash::make($newPassword);
            $user->save();

            // Delete reset token
            $this->tokenService->deletePasswordResetToken($email);

            // Revoke all tokens for security
            $user->tokens()->delete();

            return [
                'success' => true,
                'message' => 'Password reset successfully! You can now log in with your new password.',
            ];
        } catch (\Exception $e) {
            Log::error('Password reset failed', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Change user password
     *
     * @return array ['success' => bool, 'message' => string]
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): array
    {
        if (! Hash::check($currentPassword, $user->password)) {
            return [
                'success' => false,
                'message' => 'Current password is incorrect',
            ];
        }

        try {
            $user->password = Hash::make($newPassword);
            $user->save();

            return [
                'success' => true,
                'message' => 'Password changed successfully',
            ];
        } catch (\Exception $e) {
            Log::error('Password change failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete user account
     *
     * @return array ['success' => bool, 'message' => string]
     */
    public function deleteAccount(User $user, string $password): array
    {
        if (! Hash::check($password, $user->password)) {
            return [
                'success' => false,
                'message' => 'Password is incorrect',
            ];
        }

        try {
            // Delete user tokens
            $user->tokens()->delete();

            // Delete user interactions
            ArticleInteraction::where('user_id', $user->id)->delete();

            // Delete articles if user is an author
            $author = Author::where('user_id', $user->id)->first();
            if ($author) {
                Article::where('author_id', $author->id)->delete();
            }

            // Delete user
            $user->delete();

            return [
                'success' => true,
                'message' => 'Account deleted successfully',
            ];
        } catch (\Exception $e) {
            Log::error('Account deletion failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
