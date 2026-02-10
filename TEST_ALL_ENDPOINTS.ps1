# Complete API Endpoint Testing Script
$BaseUrl = "http://localhost:8000/api"

$results = @()

function Test-Endpoint {
    param(
        [string]$Method,
        [string]$Endpoint,
        [string]$Description,
        [string]$Token = $null,
        [object]$Body = $null,
        [bool]$ShouldFail = $false
    )
    
    $url = "$BaseUrl$Endpoint"
    $headers = @{'Content-Type' = 'application/json'}
    if ($Token) { $headers['Authorization'] = "Bearer $Token" }
    
    try {
        if ($Method -eq 'GET') {
            $response = Invoke-WebRequest -Uri $url -Method GET -Headers $headers -UseBasicParsing
        } else {
            $bodyJson = $Body | ConvertTo-Json
            $response = Invoke-WebRequest -Uri $url -Method $Method -Body $bodyJson -Headers $headers -UseBasicParsing
        }
        
        $status = $response.StatusCode
        $statusText = "[OK $status]"
        
        $results += [PSCustomObject]@{
            Method = $Method
            Endpoint = $Endpoint
            Description = $Description
            Status = $status
            Result = "PASS"
        }
        
        Write-Host "$statusText $Method $Endpoint - $Description"
        return $response.Content | ConvertFrom-Json
    }
    catch {
        $statusCode = $_.Exception.Response.StatusCode.Value__
        $statusText = "[FAIL $statusCode]"
        if ($ShouldFail) { $statusText = "[OK $statusCode (expected)]" }
        
        $results += [PSCustomObject]@{
            Method = $Method
            Endpoint = $Endpoint
            Description = $Description
            Status = $statusCode
            Result = if ($ShouldFail) { "PASS" } else { "FAIL" }
        }
        
        Write-Host "$statusText $Method $Endpoint - $Description"
        return $null
    }
}

Write-Host "`n========== PUBLIC ENDPOINTS (No Auth Required) ==========`n"

Test-Endpoint -Method GET -Endpoint "/team-members" -Description "Get team members"
Test-Endpoint -Method GET -Endpoint "/categories" -Description "Get all categories"
Test-Endpoint -Method GET -Endpoint "/articles/public" -Description "Get public articles (paginated)"
Test-Endpoint -Method GET -Endpoint "/articles/public?page=1" -Description "Get public articles page 1"
Test-Endpoint -Method GET -Endpoint "/authors" -Description "Get all authors"
Test-Endpoint -Method GET -Endpoint "/latest-articles" -Description "Get latest 6 articles"
Test-Endpoint -Method GET -Endpoint "/articles/search?q=test" -Description "Search articles"
Test-Endpoint -Method GET -Endpoint "/debug-articles" -Description "Debug articles (all/published/draft)"
Test-Endpoint -Method GET -Endpoint "/test-drafts" -Description "Get test drafts"

Write-Host "`n========== AUTH ENDPOINTS ==========`n"

# Test login
$loginResult = Test-Endpoint -Method POST -Endpoint "/login" -Description "Login API" -Body @{email="apitest@example.com"; password="Password123"}
if ($loginResult.token) {
    $token = $loginResult.token
    Write-Host "[INFO] Retrieved token: $($token.Substring(0, 20))..."
} else {
    Write-Host "[ERROR] Failed to retrieve token"
    exit
}

# Test duplicate register
Test-Endpoint -Method POST -Endpoint "/register" -Description "Register duplicate user (should fail)" -Body @{name="Test"; email="apitest@example.com"; password="Pass123"; password_confirmation="Pass123"} -ShouldFail $true

Write-Host "`n========== PROTECTED ENDPOINTS (Auth Required) ==========`n"

Test-Endpoint -Method GET -Endpoint "/user" -Description "Get current user profile" -Token $token
Test-Endpoint -Method POST -Endpoint "/logout" -Description "Logout API" -Token $token

# New login for further tests
$loginResult2 = Test-Endpoint -Method POST -Endpoint "/login" -Description "Re-login for continued testing" -Body @{email="apitest@example.com"; password="Password123"}
$token = $loginResult2.token

Test-Endpoint -Method GET -Endpoint "/articles" -Description "Get authenticated user articles" -Token $token
Test-Endpoint -Method GET -Endpoint "/user/liked-articles" -Description "Get user liked articles" -Token $token
Test-Endpoint -Method GET -Endpoint "/user/shared-articles" -Description "Get user shared articles" -Token $token
Test-Endpoint -Method GET -Endpoint "/logs" -Description "Get logs" -Token $token
Test-Endpoint -Method GET -Endpoint "/tags" -Description "Get all tags" -Token $token

Write-Host "`n========== ARTICLE CRUD OPERATIONS ==========`n"

# Test create article
$articleBody = @{
    title = "Test Article $(Get-Date -Format 'yyyyMMddHHmmss')"
    content = "This is test content"
    excerpt = "Test excerpt"
    status = "draft"
}
$createArticleResult = Test-Endpoint -Method POST -Endpoint "/articles" -Description "Create article" -Token $token -Body $articleBody
if ($createArticleResult.id) {
    $articleId = $createArticleResult.id
    Write-Host "[INFO] Created article ID: $articleId"
    
    # Test get specific article
    Test-Endpoint -Method GET -Endpoint "/articles/$articleId" -Description "Get specific article" -Token $token
    
    # Test update article
    Test-Endpoint -Method PUT -Endpoint "/articles/$articleId" -Description "Update article" -Token $token -Body @{title="Updated Title"; content="Updated content"}
    
    # Test like article
    Test-Endpoint -Method POST -Endpoint "/articles/$articleId/like" -Description "Like article" -Token $token
    
    # Test delete article
    Test-Endpoint -Method DELETE -Endpoint "/articles/$articleId" -Description "Delete article" -Token $token
}

Write-Host "`n========== CATEGORY OPERATIONS ==========`n"

$catBody = @{name="Test Category $(Get-Date -Format 'yyyyMMddHHmmss')"; description="Test description"}
$createCatResult = Test-Endpoint -Method POST -Endpoint "/categories" -Description "Create category" -Token $token -Body $catBody
if ($createCatResult.id) {
    $catId = $createCatResult.id
    Write-Host "[INFO] Created category ID: $catId"
    
    Test-Endpoint -Method GET -Endpoint "/categories/$catId" -Description "Get specific category" -Token $token
    Test-Endpoint -Method PUT -Endpoint "/categories/$catId" -Description "Update category" -Token $token -Body @{name="Updated Category"; description="Updated desc"}
    Test-Endpoint -Method DELETE -Endpoint "/categories/$catId" -Description "Delete category" -Token $token
}

Write-Host "`n========== TAG OPERATIONS ==========`n"

$tagBody = @{name="TestTag$(Get-Random)"; description="Test tag"}
$createTagResult = Test-Endpoint -Method POST -Endpoint "/tags" -Description "Create tag" -Token $token -Body $tagBody
if ($createTagResult.id) {
    $tagId = $createTagResult.id
    Write-Host "[INFO] Created tag ID: $tagId"
    
    Test-Endpoint -Method GET -Endpoint "/tags/$tagId" -Description "Get specific tag" -Token $token
    Test-Endpoint -Method PUT -Endpoint "/tags/$tagId" -Description "Update tag" -Token $token -Body @{name="UpdatedTag"; description="Updated"}
    Test-Endpoint -Method DELETE -Endpoint "/tags/$tagId" -Description "Delete tag" -Token $token
}

Write-Host "`n========== SUMMARY ==========`n"

$passed = ($results | Where-Object { $_.Result -eq "PASS" }).Count
$failed = ($results | Where-Object { $_.Result -eq "FAIL" }).Count
$total = $results.Count

Write-Host "Total Tests: $total"
Write-Host "Passed: $passed"
Write-Host "Failed: $failed"

Write-Host "`nDetailed Results:"
$results | Format-Table -Property Method, Endpoint, Status, Result -AutoSize
