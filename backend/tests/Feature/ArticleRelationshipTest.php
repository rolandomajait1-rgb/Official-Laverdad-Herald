<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleRelationshipTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that an article has the correct relationships.
     *
     * @return void
     */
    public function test_article_has_correct_relationships()
    {
        // Arrange
        $user = User::factory()->create();
        $author = Author::factory()->for($user)->create();
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

        $article = Article::factory()
            ->for($author)
            ->hasAttached($category)
            ->hasAttached($tag)
            ->create();

        // Act
        $retrievedArticle = Article::with('author.user', 'categories', 'tags')->find($article->id);

        // Assert
        $this->assertTrue($retrievedArticle->author->is($author));
        $this->assertTrue($retrievedArticle->author->user->is($user));
        $this->assertTrue($retrievedArticle->categories->contains($category));
        $this->assertTrue($retrievedArticle->tags->contains($tag));
    }
}
