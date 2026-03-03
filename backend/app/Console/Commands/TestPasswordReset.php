<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Console\Command;

class TestPasswordReset extends Command
{
    protected $signature = 'password:test-reset {email}';
    protected $description = 'Test password reset flow by sending a reset email';

    public function __construct(private AuthService $authService)
    {
        parent::__construct();
    }

    public function handle()
    {
        $email = $this->argument('email');
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address');
            return 1;
        }
        
        $this->info('Testing password reset flow...');
        $this->newLine();
        
        // Check if user exists
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->warn("User with email '{$email}' not found in database.");
            $this->info('Note: The system will still send a success response (to prevent user enumeration).');
            $this->newLine();
        } else {
            $this->info("User found:");
            $this->table(
                ['Field', 'Value'],
                [
                    ['Name', $user->name],
                    ['Email', $user->email],
                    ['Verified', $user->hasVerifiedEmail() ? 'Yes' : 'No'],
                    ['Role', $user->role],
                ]
            );
            $this->newLine();
        }
        
        // Initiate password reset
        $this->info('Initiating password reset...');
        
        try {
            $result = $this->authService->initiatePasswordReset($email);
            
            if ($result['success']) {
                $this->info('✅ Password reset initiated successfully!');
                $this->newLine();
                
                if ($user) {
                    $this->info('Expected behavior:');
                    $this->line('1. Password reset email sent to: ' . $email);
                    $this->line('2. Email contains reset link to frontend');
                    $this->line('3. Link format: ' . config('app.frontend_url') . '/reset-password?token=...&email=' . urlencode($email));
                    $this->line('4. Token expires in 24 hours');
                    $this->newLine();
                    
                    $this->info('Please check the email inbox (and spam folder).');
                } else {
                    $this->warn('No email was actually sent (user does not exist).');
                }
                
                return 0;
            } else {
                $this->error('❌ Password reset failed!');
                $this->error('Message: ' . $result['message']);
                return 1;
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Password reset failed with exception!');
            $this->error('Error: ' . $e->getMessage());
            $this->newLine();
            
            $this->warn('Troubleshooting steps:');
            $this->line('1. Check email configuration: php artisan email:test ' . $email);
            $this->line('2. Review logs: storage/logs/laravel.log');
            $this->line('3. Verify SMTP credentials in .env');
            
            return 1;
        }
    }
}
