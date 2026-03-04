<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Forbidden\n");
}

$baseUrl = rtrim($argv[1] ?? getenv('API_BASE_URL') ?? 'http://localhost:8000', '/');
$tests = [
    ['name' => 'health', 'method' => 'GET', 'path' => '/up', 'expected' => 200],
    ['name' => 'categories', 'method' => 'GET', 'path' => '/api/categories', 'expected' => 200],
    ['name' => 'articles_public', 'method' => 'GET', 'path' => '/api/articles/public', 'expected' => 200],
];

function requestStatus(string $method, string $url): int
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_TIMEOUT => 20,
    ]);
    curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $status;
}

fwrite(STDOUT, "API smoke test\n");
fwrite(STDOUT, "Base URL: {$baseUrl}\n\n");

$passed = 0;
$failed = 0;

foreach ($tests as $test) {
    $status = requestStatus($test['method'], $baseUrl.$test['path']);
    $ok = $status === $test['expected'];
    fwrite(
        STDOUT,
        sprintf(
            "[%s] %s expected=%d actual=%d\n",
            $ok ? 'PASS' : 'FAIL',
            $test['name'],
            $test['expected'],
            $status
        )
    );

    if ($ok) {
        $passed++;
    } else {
        $failed++;
    }
}

fwrite(STDOUT, "\nSummary: passed={$passed} failed={$failed}\n");
