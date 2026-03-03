$baseUrl = "https://official-laverdad-herald.onrender.com/api"
$testEmail = "test_" + (Get-Date -Format "yyyyMMddHHmmss") + "@student.laverdad.edu.ph"
$testPassword = "Test123456"
$token = ""

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "OFFICIAL LAVERDAD HERALD - API TESTS" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

$results = @()

function Test-Endpoint {
    param(
        [string]$Name,
        [string]$Method,
        [string]$Url,
        [hashtable]$Headers = @{},
        [object]$Body = $null,
        [int]$ExpectedStatus = 200
    )
    
    Write-Host "Testing: $Name..." -NoNewline
    
    try {
        $params = @{
            Uri = $Url
            Method = $Method
            Headers = $Headers
            ContentType = "application/json"
            TimeoutSec = 30
        }
        
        if ($Body) {
            $params.Body = ($Body | ConvertTo-Json)
        }
        
        $response = Invoke-RestMethod @params -StatusCodeVariable statusCode
        
        if ($statusCode -eq $ExpectedStatus) {
            Write-Host " ✓ PASS" -ForegroundColor Green
            $script:results += [PSCustomObject]@{
                Endpoint = $Name
                Status = "PASS"
                Code = $statusCode
            }
            return $response
        } else {
            Write-Host " ✗ FAIL (Expected $ExpectedStatus, got $statusCode)" -ForegroundColor Red
            $script:results += [PSCustomObject]@{
                Endpoint = $Name
                Status = "FAIL"
                Code = $statusCode
            }
            return $null
        }
    } catch {
        $statusCode = $_.Exception.Response.StatusCode.value__
        if ($statusCode -eq $ExpectedStatus) {
            Write-Host " ✓ PASS (Expected error)" -ForegroundColor Green
            $script:results += [PSCustomObject]@{
                Endpoint = $Name
                Status = "PASS"
                Code = $statusCode
            }
        } else {
            Write-Host " ✗ FAIL ($($_.Exception.Message))" -ForegroundColor Red
            $script:results += [PSCustomObject]@{
                Endpoint = $Name
                Status = "FAIL"
                Code = $statusCode
            }
        }
        return $null
    }
}

# PUBLIC ENDPOINTS
Write-Host "`n--- PUBLIC ENDPOINTS ---`n" -ForegroundColor Yellow

Test-Endpoint -Name "Get Categories" -Method GET -Url "$baseUrl/categories"
Test-Endpoint -Name "Get Tags" -Method GET -Url "$baseUrl/tags"
Test-Endpoint -Name "Get Public Articles" -Method GET -Url "$baseUrl/articles/public"
Test-Endpoint -Name "Get Latest Articles" -Method GET -Url "$baseUrl/latest-articles"
Test-Endpoint -Name "Get Authors" -Method GET -Url "$baseUrl/authors"
Test-Endpoint -Name "Get Team Members" -Method GET -Url "$baseUrl/team-members"
Test-Endpoint -Name "Search Articles" -Method GET -Url "$baseUrl/articles/search?q=test"

# AUTH ENDPOINTS
Write-Host "`n--- AUTH ENDPOINTS ---`n" -ForegroundColor Yellow

# Register
$registerResponse = Test-Endpoint -Name "Register User" -Method POST -Url "$baseUrl/register" `
    -Body @{
        name = "Test User"
        email = $testEmail
        password = $testPassword
        password_confirmation = $testPassword
    } -ExpectedStatus 201

# Login (should fail - email not verified)
Test-Endpoint -Name "Login (Unverified)" -Method POST -Url "$baseUrl/login" `
    -Body @{
        email = $testEmail
        password = $testPassword
    } -ExpectedStatus 403

# Resend Verification
Test-Endpoint -Name "Resend Verification" -Method POST -Url "$baseUrl/email/resend-verification" `
    -Body @{ email = $testEmail }

# Forgot Password
Test-Endpoint -Name "Forgot Password" -Method POST -Url "$baseUrl/forgot-password" `
    -Body @{ email = $testEmail }

# Login with admin (if exists)
Write-Host "`nTrying admin login..." -ForegroundColor Cyan
$adminLogin = Test-Endpoint -Name "Admin Login" -Method POST -Url "$baseUrl/login" `
    -Body @{
        email = "admin@laverdad.edu.ph"
        password = "admin123"
    }

if ($adminLogin -and $adminLogin.token) {
    $token = $adminLogin.token
    $headers = @{
        "Authorization" = "Bearer $token"
        "Accept" = "application/json"
    }
    
    # PROTECTED ENDPOINTS
    Write-Host "`n--- PROTECTED ENDPOINTS (Admin) ---`n" -ForegroundColor Yellow
    
    Test-Endpoint -Name "Get User Info" -Method GET -Url "$baseUrl/user" -Headers $headers
    Test-Endpoint -Name "Get Articles (Auth)" -Method GET -Url "$baseUrl/articles" -Headers $headers
    Test-Endpoint -Name "Get Dashboard Stats" -Method GET -Url "$baseUrl/admin/dashboard-stats" -Headers $headers
    Test-Endpoint -Name "Get Recent Activity" -Method GET -Url "$baseUrl/admin/recent-activity" -Headers $headers
    Test-Endpoint -Name "Get Admin Stats" -Method GET -Url "$baseUrl/admin/stats" -Headers $headers
    Test-Endpoint -Name "Get Moderators" -Method GET -Url "$baseUrl/admin/moderators" -Headers $headers
    Test-Endpoint -Name "Get Users" -Method GET -Url "$baseUrl/admin/users" -Headers $headers
    Test-Endpoint -Name "Get Subscribers" -Method GET -Url "$baseUrl/subscribers" -Headers $headers
    
    # Test Article Creation
    Write-Host "`nTesting Article Creation..." -ForegroundColor Cyan
    $articleData = @{
        title = "Test Article " + (Get-Date -Format "HHmmss")
        content = "This is a test article content for endpoint testing."
        category_id = 1
        author_name = "Test Author"
        status = "published"
    }
    
    $newArticle = Test-Endpoint -Name "Create Article" -Method POST -Url "$baseUrl/articles" `
        -Headers $headers -Body $articleData -ExpectedStatus 201
    
    if ($newArticle -and $newArticle.id) {
        $articleId = $newArticle.id
        
        Test-Endpoint -Name "Get Article by ID" -Method GET -Url "$baseUrl/articles/$articleId" -Headers $headers
        Test-Endpoint -Name "Like Article" -Method POST -Url "$baseUrl/articles/$articleId/like" -Headers $headers
        Test-Endpoint -Name "Update Article" -Method PUT -Url "$baseUrl/articles/$articleId" `
            -Headers $headers -Body @{
                title = "Updated Test Article"
                content = "Updated content"
                category = "News"
                tags = "test,update"
                author = "Test Author"
            }
        Test-Endpoint -Name "Delete Article" -Method DELETE -Url "$baseUrl/articles/$articleId" -Headers $headers
    }
    
    # Logout
    Test-Endpoint -Name "Logout" -Method POST -Url "$baseUrl/logout" -Headers $headers
} else {
    Write-Host "`nSkipping protected endpoints (no admin access)" -ForegroundColor Yellow
}

# CONTACT ENDPOINTS
Write-Host "`n--- CONTACT ENDPOINTS ---`n" -ForegroundColor Yellow

Test-Endpoint -Name "Send Feedback" -Method POST -Url "$baseUrl/contact/feedback" `
    -Body @{
        name = "Test User"
        email = "test@example.com"
        message = "Test feedback message"
    }

Test-Endpoint -Name "Subscribe Newsletter" -Method POST -Url "$baseUrl/contact/subscribe" `
    -Body @{ email = "subscriber@example.com" }

# RATE LIMIT TESTS
Write-Host "`n--- RATE LIMIT TESTS ---`n" -ForegroundColor Yellow

Write-Host "Testing login rate limit (10 attempts)..." -NoNewline
$rateLimitFailed = $false
for ($i = 1; $i -le 11; $i++) {
    try {
        Invoke-RestMethod -Uri "$baseUrl/login" -Method POST `
            -Body (@{ email = "test@test.com"; password = "wrong" } | ConvertTo-Json) `
            -ContentType "application/json" -ErrorAction SilentlyContinue | Out-Null
    } catch {
        if ($i -eq 11 -and $_.Exception.Response.StatusCode.value__ -eq 429) {
            $rateLimitFailed = $true
        }
    }
}

if ($rateLimitFailed) {
    Write-Host " ✓ PASS (Rate limit working)" -ForegroundColor Green
    $results += [PSCustomObject]@{
        Endpoint = "Rate Limit Test"
        Status = "PASS"
        Code = 429
    }
} else {
    Write-Host " ✗ FAIL (Rate limit not working)" -ForegroundColor Red
    $results += [PSCustomObject]@{
        Endpoint = "Rate Limit Test"
        Status = "FAIL"
        Code = 0
    }
}

# SUMMARY
Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "TEST SUMMARY" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

$passed = ($results | Where-Object { $_.Status -eq "PASS" }).Count
$failed = ($results | Where-Object { $_.Status -eq "FAIL" }).Count
$total = $results.Count

Write-Host "Total Tests: $total" -ForegroundColor White
Write-Host "Passed: $passed" -ForegroundColor Green
Write-Host "Failed: $failed" -ForegroundColor Red
Write-Host "Success Rate: $([math]::Round(($passed/$total)*100, 2))%`n" -ForegroundColor Cyan

if ($failed -gt 0) {
    Write-Host "Failed Tests:" -ForegroundColor Red
    $results | Where-Object { $_.Status -eq "FAIL" } | Format-Table -AutoSize
}

# Save results
$results | Export-Csv -Path "test_results_$(Get-Date -Format 'yyyyMMdd_HHmmss').csv" -NoTypeInformation
Write-Host "`nResults saved to test_results_*.csv" -ForegroundColor Cyan
