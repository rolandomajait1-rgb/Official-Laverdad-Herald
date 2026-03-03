<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\MailService;
use App\Services\TokenService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestEmailSending extends Command
{
    protected $signature = 'email:test-send {email}';
    protected $description = 'Test email sending to verify SMTP configuration';

    public function handle(MailService $mailService, TokenService $tokenService)
    {
        $email = $this->argument('email');
        
        $this->info('Testing email sending...');
        $this->info('');
        
        // Display current configuration
        $this->info('Current Mail Configuration:');
        $this->line('- Mailer: ' . config('mail.default'));
        $this->line('- Host: ' . config('mail.mailers.smtp.host'));
        $this->line('- Port: ' . config('mail.mailers.smtp.port'));
        $this->line('- Encryption: ' . config('mail.mailers.smtp.encryption'));
        $this->line('- Username: ' . config('mail.mailers.smtp.username'));
        $this->line('- From Address: ' . config('mail.from.address'));
        $this->line('- Queue Connection: ' . config('queue.default'));
        $this->info('');
        
        try {
            // Create a temporary test user
            $testUser = new User([
                'name' => 'Test User',
                'email' => $email,
            ]);
            
            // Generate a test token
            $token = bin2hex(random_bytes(32));
            
            $this->info('Sending verification email to: ' . $email);
            
            // Send email
            $mailService->sendVerificationEmail($testUser, $token);
            
            $this->info('');
            $this->info('✓ Email sent successfully!');
            $this->info('');
            $this->info('Please check:');
            $this->line('1. Email inbox (and spam folder)');
            $this->line('2. Laravel logs: storage/logs/laravel.log');
            $this->line('3. Brevo dashboard: https://app.brevo.com/');
            
            return 0;
        } catch (\Exception $e) {
            $this->error('');
            $this->error('✗ Email sending failed!');
            $this->error('');
            $this->error('Error: ' . $e->getMessage());
            $this->error('');
            $this->info('Troubleshooting steps:');
            $this->line('1. Check SMTP credentials in .env');
            $this->line('2. Verify Brevo account is active');
            $this->line('3. Check Laravel logs: storage/logs/laravel.log');
            $this->line('4. Run: php artisan config:clear');
            
            Log::error('Email test failed', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return 1;
        }
    }
}
