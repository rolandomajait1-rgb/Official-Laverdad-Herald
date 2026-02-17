<?php

/**
 * API Test Script - La Verdad Herald
 * Tests all critical endpoints
 */

$baseUrl = 'http://localhost:8000';
$testResults = [];
$testEmail = 'qatest' . time() . '@student.laverdad.edu.ph';
$testPassword = 'Password123';
$authToken = null;

function makeRequest($method, $url, $data = null, $headers = []) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $headers[] = 'Content-Type: application/json';
    }
    
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'status' => $httpCode,
        'body' => json_decode($response, true),
        'raw' => $response
    ];
}

function testCase($name, $expected, $actual, $details = '') {
    global $testResults;
    $passed = $expected == $actual;
    $testResults[] = [
        'name' => $name,
        'expected' => $expected,
        'actual' => $actual,
        'passed' => $passed,
        'details' => $details
    ];
    
    $status = $passed ? '✅ PASS' : '❌ FAIL';
    echo "$status - $name\n";
    if (!$passed) {
        echo "  Expected: $expected\n";
        echo "  Actual: $actual\n";
        if ($details) echo "  Details: $details\n";
    }
    echo "\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "  LA VERDAD HERALD - API TEST SUITE\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// TEST 1: Health Check
echo "TEST 1: Health Check\n";
echo "───────────────────────────────────────────────────────────────\n";
$response = makeRequest('GET', "$baseUrl/up");
testCase('Health endpoint returns 200', 200, $response['status']);

// TEST 2: Get Categories (Public)
echo "TEST 2: Get Categories (Public)\n";
echo "───────────────────────────────────────────────────────────────\n";
$response = makeRequest('GET', "$baseUrl/api/categories");
testCase('Categories endpoint returns 200', 200, $response['status']);
testCase('Categories data is array', true, is_array($response['body']));

// TEST 3: Register with Invalid Email Domain
echo "TEST 3: Register with Invalid Email Domain\n";
echo "───────────────────────────────────────────────────────────────\n";
$response = makeRequest('POST', "$baseUrl/api/register", [
    'name' => 'Test User',
    'email' => 'test@gmail.com',
    'password' => 'Password123',
    'password_confirmation' => 'Password123'
]);
testCase('Invalid email domain returns 422', 422, $response['status']);

// TEST 4: Register with Weak Password
echo "TEST 4: Register with Weak Password\n";
echo "───────────────────────────────────────────────────────────────\n";
$response = makeRequest('POST', "$baseUrl/api/register", [
    'name' => 'Test User',
    'email' => $testEmail,
    'password' => 'weak',
    'password_confirmation' => 'weak'
]);
testCase('Weak password returns 422', 422, $response['status']);

// TEST 5: Valid Registration
echo "TEST 5: Valid Registration\n";
echo "───────────────────────────────────────────────────────────────\n";
$response = makeRequest('POST', "$baseUrl/api/register", [
    'name' => 'QA Test User',
    'email' => $testEmail,
    'password' => $testPassword,
    'password_confirmation' => $testPassword
]);
testCase('Valid registration returns 201', 201, $response['status']);
testCase('Response contains user', true, isset($response['body']['user']));

// TEST 6: Duplicate Email Registration
echo "TEST 6: Duplicate Email Registration\n";
echo "───────────────────────────────────────────────────────────────\n";
$response = makeRequest('POST', "$baseUrl/api/register", [
    'name' => 'Another User',
    'email' => $testEmail,
    'password' => $testPassword,
    'password_confirmation' => $testPassword
]);
testCase('Duplicate email returns 422', 422, $response['status']);

// TEST 7: Login Before Email Verification
echo "TEST 7: Login Before Email Verification\n";
echo "───────────────────────────────────────────────────────────────\n";
$response = makeRequest('POST', "$baseUrl/api/login", [
    'email' => $testEmail,
    'password' => $testPassword
]);
testCase('Unverified login returns 403', 403, $response['status']);
testCase('Response has requires_verification', true, isset($response['body']['requires_verification']));

// TEST 8: Login with Wrong Password
echo "TEST 8: Login with Wrong Password\n";
echo "───────────────────────────────────────────────────────────────\n";
$response = makeRequest('POST', "$baseUrl/api/login", [
    'email' => $testEmail,
    'password' => 'WrongPassword123'
]);
testCase('Wrong password returns 401', 401, $response['status']);

// TEST 9: Login with Non-existent User
echo "TEST 9: Login with Non-existent User\n";
echo "───────────────────────────────────────────────────────────────\n";
$response = makeRequest('POST', "$baseUrl/api/login", [
    'email' => 'nonexistent@student.laverdad.edu.ph',
    'password' => 'Password123'
]);
testCase('Non-existent user returns 401', 401, $response['status']);

// TEST 10: Get Articles (Public)
echo "TEST 10: Get Articles (Public)\n";
echo "───────────────────────────────────────────────────────────────\n";
$response = makeRequest('GET', "$baseUrl/api/articles");
testCase('Articles endpoint returns 200', 200, $response['status']);

// TEST 11: Get Category Articles
echo "TEST 11: Get Category Articles\n";
echo "───────────────────────────────────────────────────────────────\n";
$response = makeRequest('GET', "$baseUrl/api/categories/news/articles");
testCase('Category articles returns 200', 200, $response['status']);

// TEST 12: Search Articles
echo "TEST 12: Search Articles\n";
echo "───────────────────────────────────────────────────────────────\n";
$response = makeRequest('GET', "$baseUrl/api/search?q=test");
testCase('Search endpoint returns 200', 200, $response['status']);

// TEST 13: Access Protected Route Without Token
echo "TEST 13: Access Protected Route Without Token\n";
echo "───────────────────────────────────────────────────────────────\n";
$response = makeRequest('GET', "$baseUrl/api/user");
testCase('Protected route without token returns 401', 401, $response['status']);

// TEST 14: Forgot Password
echo "TEST 14: Forgot Password\n";
echo "───────────────────────────────────────────────────────────────\n";
$response = makeRequest('POST', "$baseUrl/api/forgot-password", [
    'email' => $testEmail
]);
testCase('Forgot password returns 200', 200, $response['status']);

// SUMMARY
echo "═══════════════════════════════════════════════════════════════\n";
echo "  TEST SUMMARY\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$total = count($testResults);
$passed = count(array_filter($testResults, fn($t) => $t['passed']));
$failed = $total - $passed;
$passRate = round(($passed / $total) * 100, 2);

echo "Total Tests: $total\n";
echo "Passed: $passed ✅\n";
echo "Failed: $failed ❌\n";
echo "Pass Rate: $passRate%\n\n";

if ($failed > 0) {
    echo "FAILED TESTS:\n";
    echo "───────────────────────────────────────────────────────────────\n";
    foreach ($testResults as $test) {
        if (!$test['passed']) {
            echo "❌ {$test['name']}\n";
            echo "   Expected: {$test['expected']}\n";
            echo "   Actual: {$test['actual']}\n\n";
        }
    }
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "Test Email Created: $testEmail\n";
echo "Test Password: $testPassword\n";
echo "═══════════════════════════════════════════════════════════════\n";
