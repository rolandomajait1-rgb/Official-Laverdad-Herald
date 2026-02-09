<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the hasRole method works correctly.
     *
     * @return void
     */
    public function test_has_role_method()
    {
        // Arrange
        $admin = User::factory()->create(['role' => 'admin']);
        $moderator = User::factory()->create(['role' => 'moderator']);
        $user = User::factory()->create(['role' => 'user']);

        // Assert
        $this->assertTrue($admin->hasRole('admin'));
        $this->assertFalse($admin->hasRole('moderator'));

        $this->assertTrue($moderator->hasRole('moderator'));
        $this->assertFalse($moderator->hasRole('admin'));

        $this->assertTrue($user->hasRole('user'));
        $this->assertFalse($user->hasRole('admin'));
    }
}
