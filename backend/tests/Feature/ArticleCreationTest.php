<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_article_with_valid_author()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $author = Author::factory()->create();
        $category = Category::factory()->create();

        $articleData = [
            'title' => 'Test Article',
            'content' => 'This is a test article.',
            'category_id' => $category->id,
            'tags' => ['test', 'tag'],
            'author_name' => $author->name,
        ];

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/articles', $articleData);

        $response->assertStatus(201)
            ->assertJsonFragment(['title' => 'Test Article']);

        $this->assertDatabaseHas('articles', [
            'title' => 'Test Article',
            'author_id' => $author->id,
        ]);
    }

    public function test_admin_cannot_create_article_with_invalid_author()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create();

        $articleData = [
            'title' => 'Test Article',
            'content' => 'This is a test article.',
            'category_id' => $category->id,
            'tags' => ['test', 'tag'],
            'author_name' => 'Invalid Author',
        ];

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/articles', $articleData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('author_name');
    }
}
