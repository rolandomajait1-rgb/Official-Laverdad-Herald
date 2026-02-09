<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicCategoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the public can retrieve a list of categories.
     *
     * @return void
     */
    public function test_public_can_get_a_list_of_categories()
    {
        // Arrange
        $categories = Category::factory()->count(3)->create();

        // Act
        $response = $this->getJson('/api/categories');

        // Assert
        $response->assertStatus(200);
        $response->assertJsonCount(3);

        foreach ($categories as $category) {
            $response->assertJsonFragment([
                'name' => $category->name,
                'description' => $category->description,
            ]);
        }
    }
}
