<?php

namespace Tests\Feature;

use App\Http\Controllers\AuthController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost']);
        URL::forceRootUrl('http://localhost');
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

    public function test_signed_verification_request_marks_email_as_verified(): void
    {
        $user = User::factory()->unverified()->create();

        $signedUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addHour(),
            [
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $request = Request::create($signedUrl, 'GET');
        $route = app('router')->getRoutes()->match($request);
        $request->setRouteResolver(fn () => $route);

        $response = app(AuthController::class)->verifyEmail($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_expired_signature_is_rejected_by_verification_route(): void
    {
        $user = User::factory()->unverified()->create();

        $expiredUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->subMinute(),
            [
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $requestUri = parse_url($expiredUrl, PHP_URL_PATH) . '?' . parse_url($expiredUrl, PHP_URL_QUERY);
        $response = $this->get($requestUri);

        $response->assertRedirect(config('app.frontend_url') . '/login?error=invalid_verification_link');
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
}
