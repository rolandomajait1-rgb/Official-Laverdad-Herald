<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that an authenticated user can retrieve a list of categories.
     *
     * @return void
     */
    public function test_authenticated_user_can_get_a_list_of_categories()
    {
        // Arrange
        $user = User::factory()->create();
        $categories = Category::factory()->count(3)->create();

        // Act
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/categories');

        // Assert
        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');

        foreach ($categories as $category) {
            $response->assertJsonFragment([
                'name' => $category->name,
                'description' => $category->description,
            ]);
        }
    }

    /**
     * Test that an authenticated user can create a category.
     *
     * @return void
     */
    public function test_authenticated_user_can_create_a_category()
    {
        // Arrange
        $user = User::factory()->create();
        $categoryData = [
            'name' => 'New Category',
            'description' => 'A description for the new category.',
        ];

        // Act
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/categories', $categoryData);

        // Assert
        $response->assertStatus(201)
            ->assertJsonFragment($categoryData);

        $this->assertDatabaseHas('categories', [
            'name' => 'New Category',
        ]);
    }

    /**
     * Test that creating a category requires a name.
     *
     * @return void
     */
    public function test_create_category_requires_a_name()
    {
        // Arrange
        $user = User::factory()->create();
        $categoryData = [
            'description' => 'A description without a name.',
        ];

        // Act
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/categories', $categoryData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    /**
     * Test that categories are paginated.
     *
     * @return void
     */
    public function test_categories_are_paginated()
    {
        // Arrange
        $user = User::factory()->create();
        Category::factory()->count(20)->create();

        // Act
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/categories?page=2');

        // Assert
        $response->assertStatus(200);
        $response->assertJsonCount(10, 'data');
        $response->assertJsonPath('meta.current_page', 2);
    }
}
