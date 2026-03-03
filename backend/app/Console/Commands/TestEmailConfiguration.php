<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TestEmailConfiguration extends Command
{
    protected $signature = 'email:test {recipient?}';
    protected $description = 'Test email configuration by sending a test email';

    public function handle()
    {
        $recipient = $this->argument('recipient') ?? $this->ask('Enter recipient email address');
        
        if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address');
            return 1;
        }
        
        $this->info('Testing email configuration...');
        $this->newLine();
        
        // Display current configuration
        $this->info('Current Mail Configuration:');
        $this->table(
            ['Setting', 'Value'],
            [
                ['Mailer', config('mail.default')],
                ['Host', config('mail.mailers.smtp.host')],
                ['Port', config('mail.mailers.smtp.port')],
                ['Encryption', config('mail.mailers.smtp.encryption')],
                ['Username', config('mail.mailers.smtp.username')],
                ['From Address', config('mail.from.address')],
                ['From Name', config('mail.from.name')],
            ]
        );
        
        $this->newLine();
        $this->info("Sending test email to: {$recipient}");
        
        try {
            Mail::raw('This is a test email from La Verdad Herald. If you received this, your email configuration is working correctly!', function ($message) use ($recipient) {
                $message->to($recipient)
                    ->subject('Test Email - La Verdad Herald');
            });
            
            $this->newLine();
            $this->info('✅ Email sent successfully!');
            $this->info('Please check the recipient inbox (and spam folder).');
            
            return 0;
            
        } catch (\Exception $e) {
            $this->newLine();
            $this->error('❌ Email sending failed!');
            $this->error('Error: ' . $e->getMessage());
            $this->newLine();
            
            $this->warn('Troubleshooting steps:');
            $this->line('1. Run: php artisan config:clear');
            $this->line('2. Verify SMTP credentials in .env file');
            $this->line('3. Check Brevo account status');
            $this->line('4. Review logs: storage/logs/laravel.log');
            
            Log::error('Test email failed', [
                'recipient' => $recipient,
                'error' => $e->getMessage()
            ]);
            
            return 1;
        }
    }
}
