<?php
// PostgreSQL Database Check
$host = 'dpg-d64rrekhg0os73df6t20-a.oregon-postgres.render.com';
$port = 5432;
$database = 'laverdad_herald_db_hvqa';
$username = 'laverdad_herald_user';
$password = 'NGH35bix0sOLopEd5xyBagoTi4MvzlsB';

// Build connection string
$connection_string = "pgsql:host=$host;port=$port;dbname=$database;sslmode=require";

try {
    $pdo = new PDO($connection_string, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    echo "✅ Connection Successful!\n";
    echo "========================\n\n";
    
    // Get database info
    $result = $pdo->query("SELECT version()");
    $version = $result->fetch(PDO::FETCH_COLUMN);
    echo "PostgreSQL Version: $version\n\n";
    
    // List all tables
    echo "📊 TABLES IN DATABASE:\n";
    echo "======================\n";
    
    $tables_query = "
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        ORDER BY table_name
    ";
    
    $tables_result = $pdo->query($tables_query);
    $tables = $tables_result->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        // Get row count for each table
        $count_result = $pdo->query("SELECT COUNT(*) FROM \"$table\"");
        $count = $count_result->fetch(PDO::FETCH_COLUMN);
        echo "  • $table ($count rows)\n";
    }
    
    echo "\n📈 DETAILED TABLE INFO:\n";
    echo "======================\n";
    
    foreach ($tables as $table) {
        $columns_query = "
            SELECT column_name, data_type, is_nullable
            FROM information_schema.columns
            WHERE table_name = '$table'
            ORDER BY ordinal_position
        ";
        
        $columns_result = $pdo->query($columns_query);
        $columns = $columns_result->fetchAll(PDO::FETCH_ASSOC);
        
        // Get row count
        $count_result = $pdo->query("SELECT COUNT(*) FROM \"$table\"");
        $count = $count_result->fetch(PDO::FETCH_COLUMN);
        
        echo "\n$table ($count rows):\n";
        echo "  Columns:\n";
        
        foreach ($columns as $col) {
            $nullable = $col['is_nullable'] === 'YES' ? 'nullable' : 'NOT NULL';
            echo "    - {$col['column_name']} ({$col['data_type']}) [$nullable]\n";
        }
    }
    
    // Check specific table data
    echo "\n\n🔍 SAMPLE DATA:\n";
    echo "================\n";
    
    $sample_tables = ['users', 'articles', 'categories', 'tags', 'authors'];
    
    foreach ($sample_tables as $table) {
        if (in_array($table, $tables)) {
            $count_result = $pdo->query("SELECT COUNT(*) FROM \"$table\"");
            $count = $count_result->fetch(PDO::FETCH_COLUMN);
            
            echo "\n$table: $count rows\n";
            
            if ($count > 0) {
                $limit = min($count, 3);
                $sample_result = $pdo->query("SELECT * FROM \"$table\" LIMIT $limit");
                $rows = $sample_result->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($rows)) {
                    echo "  Sample:\n";
                    foreach ($rows as $idx => $row) {
                        echo "    Row " . ($idx + 1) . ": " . json_encode($row) . "\n";
                    }
                }
            }
        }
    }
    
    echo "\n✅ Database is healthy and accessible!\n";
    
} catch (PDOException $e) {
    echo "❌ Connection Failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
