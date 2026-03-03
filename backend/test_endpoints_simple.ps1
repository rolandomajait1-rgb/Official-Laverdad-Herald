$baseUrl = "https://official-laverdad-herald.onrender.com/api"

Write-Host "`n=== OFFICIAL LAVERDAD HERALD - API TESTS ===`n" -ForegroundColor Cyan

$results = @()

function Test-API {
    param($Name, $Url, $Method = "GET", $Body = $null, $Headers = @{})
    
    Write-Host "Testing: $Name..." -NoNewline
    
    try {
        $params = @{
            Uri = $Url
            Method = $Method
            Headers = $Headers
            ContentType = "application/json"
        }
        
        if ($Body) {
            $params.Body = ($Body | ConvertTo-Json)
        }
        
        $response = Invoke-RestMethod @params
        Write-Host " PASS" -ForegroundColor Green
        $script:results += @{Name=$Name; Status="PASS"}
        return $response
    } catch {
        Write-Host " FAIL - $($_.Exception.Message)" -ForegroundColor Red
        $script:results += @{Name=$Name; Status="FAIL"}
        return $null
    }
}

Write-Host "--- PUBLIC ENDPOINTS ---`n" -ForegroundColor Yellow

Test-API "Get Categories" "$baseUrl/categories"
Test-API "Get Tags" "$baseUrl/tags"
Test-API "Get Public Articles" "$baseUrl/articles/public"
Test-API "Get Latest Articles" "$baseUrl/latest-articles"
Test-API "Get Authors" "$baseUrl/authors"
Test-API "Get Team Members" "$baseUrl/team-members"
Test-API "Search Articles" "$baseUrl/articles/search?q=test"

Write-Host "`n--- AUTH ENDPOINTS ---`n" -ForegroundColor Yellow

$testEmail = "test_$(Get-Date -Format 'yyyyMMddHHmmss')@student.laverdad.edu.ph"
$registerData = @{
    name = "Test User"
    email = $testEmail
    password = "Test123456"
    password_confirmation = "Test123456"
}

Test-API "Register User" "$baseUrl/register" "POST" $registerData
Test-API "Resend Verification" "$baseUrl/email/resend-verification" "POST" @{email=$testEmail}
Test-API "Forgot Password" "$baseUrl/forgot-password" "POST" @{email=$testEmail}

Write-Host "`n--- CONTACT ENDPOINTS ---`n" -ForegroundColor Yellow

Test-API "Send Feedback" "$baseUrl/contact/feedback" "POST" @{
    name = "Test"
    email = "test@test.com"
    message = "Test message"
}

Test-API "Subscribe" "$baseUrl/contact/subscribe" "POST" @{email="sub@test.com"}

Write-Host "`n--- SUMMARY ---`n" -ForegroundColor Cyan

$passed = ($results | Where-Object {$_.Status -eq "PASS"}).Count
$failed = ($results | Where-Object {$_.Status -eq "FAIL"}).Count
$total = $results.Count

Write-Host "Total: $total | Passed: $passed | Failed: $failed" -ForegroundColor White
Write-Host "Success Rate: $([math]::Round(($passed/$total)*100, 2))%`n" -ForegroundColor Cyan

if ($failed -gt 0) {
    Write-Host "Failed Tests:" -ForegroundColor Red
    $results | Where-Object {$_.Status -eq "FAIL"} | ForEach-Object { Write-Host "  - $($_.Name)" -ForegroundColor Red }
}
