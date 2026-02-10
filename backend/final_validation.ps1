# Final comprehensive endpoint validation
$endpoints = @(
    @{ method = "GET"; path = "/api/categories"; name = "Categories List" },
    @{ method = "GET"; path = "/api/authors"; name = "Authors List" },
    @{ method = "GET"; path = "/api/tags"; name = "Tags List" },
    @{ method = "GET"; path = "/api/logs"; name = "Logs List" },
    @{ method = "GET"; path = "/api/articles/public"; name = "Public Articles" },
    @{ method = "GET"; path = "/api/latest-articles"; name = "Latest Articles" },
    @{ method = "GET"; path = "/api/team-members"; name = "Team Members" },
    @{ method = "POST"; path = "/api/register"; name = "Register (public)" },
    @{ method = "POST"; path = "/api/login"; name = "Login (public)" },
    @{ method = "POST"; path = "/api/contact/subscribe"; name = "Subscribe" },
    @{ method = "GET"; path = "/api/user"; name = "Get User (protected)" },
    @{ method = "POST"; path = "/api/logout"; name = "Logout (protected)" }
)

Write-Host "=== FINAL ENDPOINT VALIDATION ===" -ForegroundColor Cyan
Write-Host "Testing $(($endpoints | Measure-Object).Count) Critical Endpoints" -ForegroundColor Cyan
Write-Host ""

$pass200 = 0
$pass201 = 0
$pass401 = 0
$pass404 = 0
$fail = 0

foreach ($endpoint in $endpoints) {
    $uri = "http://localhost:8000" + $endpoint.path
    
    try {
        $response = Invoke-WebRequest -Uri $uri -Method $endpoint.method -UseBasicParsing -ErrorAction Stop
        $code = $response.StatusCode
        Write-Host "[OK-$code] $($endpoint.method.PadRight(6)) $($endpoint.path.PadRight(30)) - $($endpoint.name)" -ForegroundColor Green
        if ($code -eq 200) { $pass200++ }
        elseif ($code -eq 201) { $pass201++ }
    } catch {
        $code = $_.Exception.Response.StatusCode.Value__
        
        if ($code -eq 401) {
            Write-Host "[AUTH] $($endpoint.method.PadRight(6)) $($endpoint.path.PadRight(30)) - $($endpoint.name)" -ForegroundColor Yellow
            $pass401++
        } elseif ($code -eq 404) {
            Write-Host "[MISS] $($endpoint.method.PadRight(6)) $($endpoint.path.PadRight(30)) - $($endpoint.name)" -ForegroundColor Yellow
            $pass404++
        } else {
            Write-Host "[ERR-$code] $($endpoint.method.PadRight(6)) $($endpoint.path.PadRight(30)) - $($endpoint.name)" -ForegroundColor Red
            $fail++
        }
    }
}

Write-Host ""
Write-Host "=== SUMMARY ===" -ForegroundColor Cyan
Write-Host "Success (200):  $pass200" -ForegroundColor Green
Write-Host "Created (201):  $pass201" -ForegroundColor Green
Write-Host "Auth (401):     $pass401" -ForegroundColor Yellow
Write-Host "Missing (404):  $pass404" -ForegroundColor Yellow
Write-Host "Errors (5xx):   $fail" -ForegroundColor Red
Write-Host ""

$total = $pass200 + $pass201 + $pass401
if ($fail -eq 0 -and $total -ge 10) {
    Write-Host "READY FOR DEPLOYMENT!" -ForegroundColor Green
} else {
    Write-Host "Issues detected" -ForegroundColor Yellow
}
