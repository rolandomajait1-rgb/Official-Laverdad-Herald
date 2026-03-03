<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $indexes = [
            ['table' => 'articles', 'name' => 'articles_status_index', 'columns' => ['status']],
            ['table' => 'articles', 'name' => 'articles_published_at_index', 'columns' => ['published_at']],
            ['table' => 'articles', 'name' => 'articles_status_published_at_index', 'columns' => ['status', 'published_at']],
            ['table' => 'article_interactions', 'name' => 'article_interactions_article_id_type_index', 'columns' => ['article_id', 'type']],
            ['table' => 'article_interactions', 'name' => 'article_interactions_user_id_article_id_type_index', 'columns' => ['user_id', 'article_id', 'type']],
        ];

        foreach ($indexes as $index) {
            $exists = DB::select(
                "SELECT 1 FROM pg_indexes WHERE tablename = ? AND indexname = ?",
                [$index['table'], $index['name']]
            );

            if (empty($exists)) {
                $columns = implode(', ', array_map(fn($c) => "\"$c\"", $index['columns']));
                DB::statement("CREATE INDEX \"{$index['name']}\" ON \"{$index['table']}\" ($columns)");
            }
        }
    }

    public function down(): void
    {
        $indexes = [
            'articles_status_index',
            'articles_published_at_index',
            'articles_status_published_at_index',
            'article_interactions_article_id_type_index',
            'article_interactions_user_id_article_id_type_index',
        ];

        foreach ($indexes as $indexName) {
            DB::statement("DROP INDEX IF EXISTS \"$indexName\"");
        }
    }
};
