<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->index('status');
            $table->index('published_at');
            $table->index(['status', 'published_at']);
        });

        Schema::table('article_interactions', function (Blueprint $table) {
            $table->index(['article_id', 'type']);
            $table->index(['user_id', 'article_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['published_at']);
            $table->dropIndex(['status', 'published_at']);
        });

        Schema::table('article_interactions', function (Blueprint $table) {
            $table->dropIndex(['article_id', 'type']);
            $table->dropIndex(['user_id', 'article_id', 'type']);
        });
    }
};
