<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration adds performance indexes to improve query speed
     * and support higher user capacity.
     */
    public function up(): void
    {
        // Add index on articles.published_at for faster sorting
        Schema::table('articles', function (Blueprint $table) {
            $table->index('published_at', 'idx_articles_published_at');
            $table->index('status', 'idx_articles_status');
            $table->index(['author_id', 'status'], 'idx_articles_author_status');
            $table->index('view_count', 'idx_articles_view_count');
        });

        // Add indexes on pivot tables for faster joins
        Schema::table('article_category', function (Blueprint $table) {
            $table->index('article_id', 'idx_article_category_article');
            $table->index('category_id', 'idx_article_category_category');
        });

        Schema::table('article_tag', function (Blueprint $table) {
            $table->index('article_id', 'idx_article_tag_article');
            $table->index('tag_id', 'idx_article_tag_tag');
        });

        // Add index on subscribers.status for faster filtering
        Schema::table('subscribers', function (Blueprint $table) {
            $table->index('status', 'idx_subscribers_status');
            $table->index('subscribed_at', 'idx_subscribers_subscribed_at');
        });

        // Add index on sessions for faster cleanup
        Schema::table('sessions', function (Blueprint $table) {
            $table->index('last_activity', 'idx_sessions_last_activity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropIndex('idx_articles_published_at');
            $table->dropIndex('idx_articles_status');
            $table->dropIndex('idx_articles_author_status');
            $table->dropIndex('idx_articles_view_count');
        });

        Schema::table('article_category', function (Blueprint $table) {
            $table->dropIndex('idx_article_category_article');
            $table->dropIndex('idx_article_category_category');
        });

        Schema::table('article_tag', function (Blueprint $table) {
            $table->dropIndex('idx_article_tag_article');
            $table->dropIndex('idx_article_tag_tag');
        });

        Schema::table('subscribers', function (Blueprint $table) {
            $table->dropIndex('idx_subscribers_status');
            $table->dropIndex('idx_subscribers_subscribed_at');
        });

        Schema::table('sessions', function (Blueprint $table) {
            $table->dropIndex('idx_sessions_last_activity');
        });
    }
};
