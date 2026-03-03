<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * Property 2: Preservation Tests - Non-Image Article Behavior
 * 
 * These tests verify that non-buggy scenarios continue to work correctly after the fix.
 * EXPECTED OUTCOME: Tests PASS (confirms baseline behavior is preserved)
 */
class ArticleImagePreservationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Case 1: Articles without images return placeholder URL
     */
    public function test_articles_without_images_return_placeholder_url()
    {
        // Arrange
        $author = Author::factory()->create();
        
        // Create article without featured_image
        $article = Article::factory()->create([
            'author_id' => $author->id,
            'featured_image' => null,
        ]);
        
        // Act
        $imageUrl = $article->featured_image_url;
        
        // Assert
        $this->assertEquals(
            'https://placehold.co/800x600/0891b2/ffffff?text=La+Verdad+Herald',
            $imageUrl,
            'Articles without images should return placeholder URL'
        );
    }

    /**
     * Test Case 2: Articles with existing HTTP/HTTPS URLs return unchanged
     */
    public function test_articles_with_existing_urls_return_unchanged()
    {
        // Arrange
        $author = Author::factory()->create();
        
        $testUrls = [
            'https://example.com/image.jpg',
            'http://example.com/image.png',
            'https://res.cloudinary.com/demo/image/upload/sample.jpg',
        ];
        
        foreach ($testUrls as $url) {
            // Create article with full URL
            $article = Article::factory()->create([
                'author_id' => $author->id,
                'featured_image' => $url,
            ]);
            
            // Act
            $imageUrl = $article->featured_image_url;
            
            // Assert
            $this->assertEquals($url, $imageUrl,
                "Article with URL {$url} should return the same URL unchanged");
        }
    }

    /**
     * Test Case 3: Image validation constraints remain enforced
     */
    public function test_image_validation_constraints_are_enforced()
    {
        // Arrange
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create();
        
        // Test invalid file type (gif)
        $invalidTypeImage = UploadedFile::fake()->image('test.gif');
        
        $articleData = [
            'title' => 'Test Article',
            'content' => 'Test content',
            'category_id' => $category->id,
            'tags' => ['test'],
            'author_name' => $admin->name,
            'featured_image' => $invalidTypeImage,
        ];
        
        // Act & Assert - Invalid file type should be rejected
        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/articles', $articleData);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors('featured_image');
        
        // Test oversized file (>5120KB)
        $oversizedImage = UploadedFile::fake()->image('large.jpg')->size(6000);
        
        $articleData['featured_image'] = $oversizedImage;
        
        // Act & Assert - Oversized file should be rejected
        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/articles', $articleData);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors('featured_image');
    }

    /**
     * Test Case 4: Articles queried with eager loading append featured_image_url
     */
    public function test_articles_with_eager_loading_append_featured_image_url()
    {
        // Arrange
        $author = Author::factory()->create();
        $category = Category::factory()->create();
        
        $article = Article::factory()->create([
            'author_id' => $author->id,
            'featured_image' => 'https://example.com/test.jpg',
            'status' => 'published',
            'published_at' => now(),
        ]);
        $article->categories()->attach($category);
        
        // Act - Query with eager loading
        $response = $this->getJson('/api/articles/public');
        
        // Assert
        $response->assertStatus(200);
        
        $articles = $response->json('data');
        $this->assertNotEmpty($articles);
        
        $foundArticle = collect($articles)->firstWhere('id', $article->id);
        $this->assertNotNull($foundArticle, 'Article should be in response');
        $this->assertArrayHasKey('featured_image_url', $foundArticle,
            'Response should include featured_image_url attribute');
        $this->assertEquals('https://example.com/test.jpg', $foundArticle['featured_image_url']);
    }

    /**
     * Test Case 5: Valid image uploads are accepted
     */
    public function test_valid_image_uploads_are_accepted()
    {
        // Arrange
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create();
        
        $validFormats = ['jpg', 'jpeg', 'png'];
        
        foreach ($validFormats as $format) {
            $validImage = UploadedFile::fake()->image("test.{$format}", 800, 600)->size(1000);
            
            $articleData = [
                'title' => "Test Article {$format}",
                'content' => 'Test content',
                'category_id' => $category->id,
                'tags' => ['test'],
                'author_name' => $admin->name,
                'featured_image' => $validImage,
            ];
            
            // Act
            $response = $this->actingAs($admin, 'sanctum')
                ->postJson('/api/articles', $articleData);
            
            // Assert
            $response->assertStatus(201);
            
            $article = Article::latest()->first();
            $this->assertNotNull($article->featured_image,
                "Valid {$format} image should be uploaded successfully");
        }
    }
}
