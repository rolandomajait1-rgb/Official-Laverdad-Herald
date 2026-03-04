<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VerificationToken;
use App\Services\MailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class EmailVerificationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost']);
        config(['app.frontend_url' => 'http://localhost:5173']);
    }

    public function test_api_register_rejects_non_school_email_domain(): void
    {
        $payload = [
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_unverified_user_cannot_login_via_api(): void
    {
        $user = User::factory()->unverified()->create([
            'password' => Hash::make('Password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'Password123',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'requires_verification' => true,
        ]);
    }

    public function test_token_verification_request_marks_email_as_verified(): void
    {
        $user = User::factory()->unverified()->create();
        $token = VerificationToken::create([
            'user_id' => $user->id,
            'token' => 'test_token_123',
            'expires_at' => now()->addHour(),
        ]);

        $response = $this->get('/api/email/verify-token?token='.$token->token);

        $response->assertRedirect('http://localhost:5173/login?verified=1');
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertNull(VerificationToken::where('token', $token->token)->first());
    }

    public function test_expired_token_is_rejected_by_verification_route(): void
    {
        $user = User::factory()->unverified()->create();
        VerificationToken::create([
            'user_id' => $user->id,
            'token' => 'expired_token_123',
            'expires_at' => now()->subMinute(),
        ]);

        $response = $this->get('/api/email/verify-token?token=expired_token_123');

        $response->assertRedirect(config('app.frontend_url').'/login?error=invalid_or_expired_token');
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_resend_verification_returns_generic_response_for_existing_and_missing_email(): void
    {
        $existing = User::factory()->unverified()->create([
            'email' => 'existing@student.laverdad.edu.ph',
        ]);

        $missingEmail = 'missing@student.laverdad.edu.ph';

        $existingResponse = $this->postJson('/api/email/resend-verification', [
            'email' => $existing->email,
        ]);

        $missingResponse = $this->postJson('/api/email/resend-verification', [
            'email' => $missingEmail,
        ]);

        $expected = [
            'message' => 'If an unverified account exists for that email, a verification email has been sent.',
        ];

        $existingResponse->assertStatus(200)->assertJson($expected);
        $missingResponse->assertStatus(200)->assertJson($expected);
    }

    public function test_api_register_returns_503_and_rolls_back_when_verification_email_fails(): void
    {
        $this->mock(MailService::class, function ($mock): void {
            $mock->shouldReceive('sendVerificationEmail')
                ->once()
                ->andThrow(new TransportException('SMTP unavailable'));
        });

        $payload = [
            'name' => 'Delivery Failure User',
            'email' => 'deliveryfail@student.laverdad.edu.ph',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(503);
        $response->assertJson([
            'message' => 'Registration is temporarily unavailable because verification email could not be sent. Please try again shortly.',
        ]);

        $this->assertDatabaseMissing('users', [
            'email' => 'deliveryfail@student.laverdad.edu.ph',
        ]);
    }
}
