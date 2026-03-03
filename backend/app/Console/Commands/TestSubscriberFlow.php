<?php

namespace App\Console\Commands;

use App\Services\SubscriberService;
use Illuminate\Console\Command;

class TestSubscriberFlow extends Command
{
    protected $signature = 'subscriber:test {email} {--name=}';
    protected $description = 'Test subscriber flow by subscribing an email';

    public function __construct(private SubscriberService $subscriberService)
    {
        parent::__construct();
    }

    public function handle()
    {
        $email = $this->argument('email');
        $name = $this->option('name');
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address');
            return 1;
        }
        
        $this->info('Testing subscriber flow...');
        $this->newLine();
        
        // Test subscription
        $this->info("Subscribing: {$email}");
        if ($name) {
            $this->info("Name: {$name}");
        }
        $this->newLine();
        
        try {
            $result = $this->subscriberService->subscribe($email, $name);
            
            if ($result['success']) {
                $this->info('✅ ' . $result['message']);
                $this->newLine();
                
                $subscriber = $result['subscriber'];
                $this->info('Subscriber Details:');
                $this->table(
                    ['Field', 'Value'],
                    [
                        ['ID', $subscriber->id],
                        ['Email', $subscriber->email],
                        ['Name', $subscriber->name ?? 'N/A'],
                        ['Status', $subscriber->status],
                        ['Subscribed At', $subscriber->subscribed_at->format('Y-m-d H:i:s')],
                        ['Unsubscribe Token', substr($subscriber->unsubscribe_token, 0, 20) . '...'],
                    ]
                );
                
                $this->newLine();
                $this->info('Expected behavior:');
                $this->line('1. Welcome email sent to: ' . $email);
                $this->line('2. Email contains unsubscribe link');
                $this->line('3. Unsubscribe URL: ' . config('app.frontend_url') . '/unsubscribe?token=' . $subscriber->unsubscribe_token);
                $this->newLine();
                
                $this->info('Please check the email inbox (and spam folder).');
                
                // Show statistics
                $stats = $this->subscriberService->getStatistics();
                $this->newLine();
                $this->info('Subscriber Statistics:');
                $this->table(
                    ['Status', 'Count'],
                    [
                        ['Total', $stats['total']],
                        ['Active', $stats['active']],
                        ['Inactive', $stats['inactive']],
                        ['Unsubscribed', $stats['unsubscribed']],
                    ]
                );
                
                return 0;
            } else {
                $this->warn('⚠️  ' . $result['message']);
                return 0;
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Subscription failed with exception!');
            $this->error('Error: ' . $e->getMessage());
            $this->newLine();
            
            $this->warn('Troubleshooting steps:');
            $this->line('1. Check email configuration: php artisan email:test ' . $email);
            $this->line('2. Review logs: storage/logs/laravel.log');
            $this->line('3. Run migration: php artisan migrate');
            
            return 1;
        }
    }
}
