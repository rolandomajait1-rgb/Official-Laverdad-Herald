$BASE_URL = "http://localhost:8000"
$API_URL = "$BASE_URL/api"

Write-Host "=== Testing All Endpoints ===" -ForegroundColor Cyan

# Test 1: CSRF Cookie
Write-Host "`n[1] GET /sanctum/csrf-cookie" -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "$BASE_URL/sanctum/csrf-cookie" -Method GET -UseBasicParsing
    Write-Host "Status: $($response.StatusCode)" -ForegroundColor Green
} catch {
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 2: Register
Write-Host "`n[2] POST /api/register" -ForegroundColor Yellow
try {
    $body = @{
        name = "Test User"
        email = "test@example.com"
        password = "TestPassword123"
        password_confirmation = "TestPassword123"
    } | ConvertTo-Json
    
    $response = Invoke-WebRequest -Uri "$API_URL/register" -Method POST -Body $body -ContentType "application/json" -UseBasicParsing
    Write-Host "Status: $($response.StatusCode)" -ForegroundColor Green
} catch {
    Write-Host "Error: $($_.Exception.Response.StatusCode)" -ForegroundColor Red
}

# Test 3: Login
Write-Host "`n[3] POST /api/login" -ForegroundColor Yellow
try {
    $body = @{
        email = "test@example.com"
        password = "TestPassword123"
    } | ConvertTo-Json
    
    $response = Invoke-WebRequest -Uri "$API_URL/login" -Method POST -Body $body -ContentType "application/json" -UseBasicParsing
    Write-Host "Status: $($response.StatusCode)" -ForegroundColor Green
} catch {
    Write-Host "Error: $($_.Exception.Response.StatusCode)" -ForegroundColor Red
}

# Test 4: Get Public Articles
Write-Host "`n[4] GET /api/articles/public" -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "$API_URL/articles/public" -Method GET -UseBasicParsing
    Write-Host "Status: $($response.StatusCode)" -ForegroundColor Green
} catch {
    Write-Host "Error: $($_.Exception.Response.StatusCode)" -ForegroundColor Red
}

# Test 5: Get Categories
Write-Host "`n[5] GET /api/categories" -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "$API_URL/categories" -Method GET -UseBasicParsing
    Write-Host "Status: $($response.StatusCode)" -ForegroundColor Green
} catch {
    Write-Host "Error: $($_.Exception.Response.StatusCode)" -ForegroundColor Red
}

# Test 6: Get Tags
Write-Host "`n[6] GET /api/tags" -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "$API_URL/tags" -Method GET -UseBasicParsing
    Write-Host "Status: $($response.StatusCode)" -ForegroundColor Green
} catch {
    Write-Host "Error: $($_.Exception.Response.StatusCode)" -ForegroundColor Red
}

# Test 7: Get Latest Articles
Write-Host "`n[7] GET /api/latest-articles" -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "$API_URL/latest-articles" -Method GET -UseBasicParsing
    Write-Host "Status: $($response.StatusCode)" -ForegroundColor Green
} catch {
    Write-Host "Error: $($_.Exception.Response.StatusCode)" -ForegroundColor Red
}

# Test 8: Get Authors
Write-Host "`n[8] GET /api/authors" -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "$API_URL/authors" -Method GET -UseBasicParsing
    Write-Host "Status: $($response.StatusCode)" -ForegroundColor Green
} catch {
    Write-Host "Error: $($_.Exception.Response.StatusCode)" -ForegroundColor Red
}

# Test 9: Get Team Members
Write-Host "`n[9] GET /api/team-members" -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "$API_URL/team-members" -Method GET -UseBasicParsing
    Write-Host "Status: $($response.StatusCode)" -ForegroundColor Green
} catch {
    Write-Host "Error: $($_.Exception.Response.StatusCode)" -ForegroundColor Red
}

Write-Host "`n=== Test Complete ===" -ForegroundColor Cyan
