<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Article;
use App\Models\Category;
use App\Models\Author;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_articles_endpoint()
    {
        $response = $this->getJson('/api/articles/public');
        $response->assertStatus(200);
    }

    public function test_categories_endpoint()
    {
        $response = $this->getJson('/api/categories');
        $response->assertStatus(200);
    }

    public function test_latest_articles_endpoint()
    {
        $response = $this->getJson('/api/latest-articles');
        $response->assertStatus(200)->assertJsonStructure([]);
    }

    public function test_login_endpoint_validation()
    {
        $response = $this->postJson('/api/login', []);
        $response->assertStatus(422);
    }

    public function test_authenticated_user_endpoint()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/user');
        $response->assertStatus(200)->assertJson(['email' => $user->email]);
    }

    public function test_admin_dashboard_stats_requires_auth()
    {
        $response = $this->getJson('/api/admin/dashboard-stats');
        $response->assertStatus(401);
    }

    public function test_admin_can_access_dashboard_stats()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/admin/dashboard-stats');
        $response->assertStatus(200)->assertJsonStructure(['users', 'articles', 'drafts', 'views', 'likes']);
    }

    public function test_article_search_endpoint()
    {
        $response = $this->getJson('/api/articles/search?q=test');
        $response->assertStatus(200)->assertJsonStructure(['data']);
    }

    public function test_contact_feedback_requires_data()
    {
        $response = $this->postJson('/api/contact/feedback', []);
        $response->assertStatus(422);
    }
}
