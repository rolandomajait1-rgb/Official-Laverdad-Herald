<?php
// Load .env using Laravel's env helper
$envFile = file_get_contents('.env');
$lines = explode("\n", $envFile);
$env = [];

foreach ($lines as $line) {
    $line = trim($line);
    if ($line && strpos($line, '=') !== false && $line[0] !== '#') {
        list($key, $value) = explode('=', $line, 2);
        $env[trim($key)] = trim($value, '\'"');
    }
}

// Create PDO connection
try {
    $dsn = "pgsql:host=" . $env['DB_HOST'] . ";port=" . $env['DB_PORT'] . ";dbname=" . $env['DB_DATABASE'];
    $pdo = new PDO($dsn, $env['DB_USERNAME'], $env['DB_PASSWORD']);
    echo "=== Database Connection ===\n";
    echo "Connected to: " . $env['DB_DATABASE'] . " on " . $env['DB_HOST'] . "\n\n";
    
    // Get tables
    echo "=== Checking Tables ===\n";
    $stmt = $pdo->query("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        ORDER BY table_name
    ");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Total tables found: " . count($tables) . "\n\n";
    
    $targetTables = ['authors', 'tags', 'logs', 'articles', 'categories'];
    foreach ($targetTables as $target) {
        if (in_array($target, $tables)) {
            $countStmt = $pdo->query("SELECT COUNT(*) FROM " . $target);
            $count = $countStmt->fetchColumn();
            echo "✓ $target (rows: $count)\n";
        } else {
            echo "✗ $target (MISSING)\n";
        }
    }
    
    echo "\n=== All Tables ===\n";
    foreach ($tables as $table) {
        echo "- " . $table . "\n";
    }
    
} catch (Exception $e) {
    echo "Error connecting to database: " . $e->getMessage() . "\n";
    exit(1);
}
