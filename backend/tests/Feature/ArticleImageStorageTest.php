<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Models\User;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

/**
 * Property 1: Bug Condition Exploration - Cloudinary Storage Verification
 * 
 * This test verifies that images are stored in Cloudinary (not local filesystem).
 * EXPECTED OUTCOME AFTER FIX: Test PASSES (images stored in Cloudinary)
 */
class ArticleImageStorageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that article images are uploaded to Cloudinary (not local filesystem)
     * 
     * Bug Condition: Images stored locally (path starts with 'articles/')
     * Expected Behavior: Images stored in Cloudinary (URL starts with 'https://res.cloudinary.com/')
     */
    public function test_article_images_are_stored_in_cloudinary_not_local_filesystem()
    {
        // Mock Cloudinary to avoid interactive prompts
        $mockResult = Mockery::mock();
        $mockResult->shouldReceive('getSecurePath')
            ->andReturn('https://res.cloudinary.com/da9wvkqcl/image/upload/v1234567890/articles/test-image.jpg');
        
        Cloudinary::shouldReceive('upload')
            ->andReturn($mockResult);
        
        // Arrange
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create();
        
        // Test with various image formats and sizes
        $testCases = [
            ['format' => 'jpg', 'size' => 100],  // Small JPEG
            ['format' => 'jpeg', 'size' => 500], // Medium JPEG
            ['format' => 'png', 'size' => 1000], // Large PNG
        ];
        
        foreach ($testCases as $testCase) {
            // Create a fake image file
            $image = UploadedFile::fake()->image("test-image.{$testCase['format']}", 800, 600)
                ->size($testCase['size']);
            
            $articleData = [
                'title' => "Test Article with {$testCase['format']} image",
                'content' => 'This is a test article with an uploaded image.',
                'category_id' => $category->id,
                'tags' => ['test'],
                'author_name' => $admin->name,
                'featured_image' => $image,
            ];
            
            // Act
            $response = $this->actingAs($admin, 'sanctum')
                ->postJson('/api/articles', $articleData);
            
            // Assert
            $response->assertStatus(201);
            
            $article = Article::latest()->first();
            
            // CRITICAL ASSERTION: Image should be stored in Cloudinary, not local filesystem
            $this->assertNotNull($article->featured_image, 
                "Featured image should not be null for {$testCase['format']} upload");
            
            // Bug condition: If path starts with 'articles/', it's stored locally (BUG)
            $this->assertStringStartsNotWith('articles/', $article->featured_image,
                "Image should NOT be stored in local filesystem (articles/) for {$testCase['format']} upload");
            
            // Expected behavior: Should be a Cloudinary URL
            $this->assertStringStartsWith('https://res.cloudinary.com/', $article->featured_image,
                "Image should be stored in Cloudinary (https://res.cloudinary.com/) for {$testCase['format']} upload");
        }
    }

    /**
     * Test that article image updates use Cloudinary storage
     */
    public function test_article_image_updates_use_cloudinary_storage()
    {
        // Mock Cloudinary to avoid interactive prompts
        $mockResult = Mockery::mock();
        $mockResult->shouldReceive('getSecurePath')
            ->andReturn('https://res.cloudinary.com/da9wvkqcl/image/upload/v1234567890/articles/updated-image.jpg');
        
        Cloudinary::shouldReceive('upload')
            ->andReturn($mockResult);
        
        // Arrange
        $admin = User::factory()->create(['role' => 'admin']);
        $author = Author::factory()->create(['user_id' => $admin->id]);
        $category = Category::factory()->create();
        
        $article = Article::factory()->create([
            'author_id' => $author->id,
            'featured_image' => null,
        ]);
        $article->categories()->attach($category);
        
        // Create a new image for update
        $newImage = UploadedFile::fake()->image('updated-image.jpg', 800, 600);
        
        $updateData = [
            'title' => $article->title,
            'content' => $article->content,
            'category' => $category->name,
            'tags' => 'test,update',
            'author' => $admin->name,
            'featured_image' => $newImage,
        ];
        
        // Act
        $response = $this->actingAs($admin, 'sanctum')
            ->putJson("/api/articles/{$article->id}", $updateData);
        
        // Assert
        $response->assertStatus(200);
        
        $article->refresh();
        
        // CRITICAL ASSERTION: Updated image should be in Cloudinary
        $this->assertNotNull($article->featured_image);
        $this->assertStringStartsNotWith('articles/', $article->featured_image,
            'Updated image should NOT be stored in local filesystem');
        $this->assertStringStartsWith('https://res.cloudinary.com/', $article->featured_image,
            'Updated image should be stored in Cloudinary');
    }
}
