# Comprehensive endpoint test
$endpoints = @(
    # Public endpoints
    "GET /api/categories",
    "GET /api/authors",
    "GET /api/tags",
    "GET /api/logs",
    "GET /api/articles/public",
    "GET /api/latest-articles",
    
    # Auth endpoints
    "POST /api/register",
    "POST /api/login",
    
    # Protected endpoints
    "GET /api/user",
    "POST /api/logout",
    
    # Contact endpoints
    "POST /api/contact/feedback",
    "POST /api/contact/subscribe",
    
    # Team members
    "GET /api/team-members",
    
    # Category detail
    "GET /api/categories/news",
    
    # Author detail
    "GET /api/authors/john-doe",
    
    # Article detail
    "GET /api/articles/1",
    "GET /api/articles/author-public/1",
    
    # Tag detail
    "GET /api/tags/breaking-news"
)

Write-Host "=== La Verdad Herald API Endpoint Test ===" -ForegroundColor Cyan
Write-Host "Testing $($endpoints.Count) endpoints" -ForegroundColor Cyan
Write-Host ""

$results = @()

foreach ($endpoint in $endpoints) {
    $parts = $endpoint -split " /api/"
    $method = $parts[0]
    $path = "/api/" + $parts[1]
    $uri = "http://localhost:8000" + $path
    
    try {
        $response = Invoke-WebRequest -Uri $uri -Method $method -UseBasicParsing -ErrorAction Stop
        Write-Host "[OK] $endpoint -> $($response.StatusCode)" -ForegroundColor Green
        $results += @{ endpoint = $endpoint; status = $($response.StatusCode); result = "OK" }
    } catch {
        $statusCode = $_.Exception.Response.StatusCode.Value__ 
        if ($statusCode -eq 401) {
            Write-Host "[AUTH] $endpoint -> $statusCode (expected for protected endpoints)" -ForegroundColor Yellow
            $results += @{ endpoint = $endpoint; status = $statusCode; result = "AUTH" }
        } elseif ($statusCode -eq 404) {
            Write-Host "[MISSING] $endpoint -> 404" -ForegroundColor Yellow
            $results += @{ endpoint = $endpoint; status = 404; result = "MISSING" }
        } else {
            Write-Host "[ERROR] $endpoint -> $statusCode" -ForegroundColor Red
            $results += @{ endpoint = $endpoint; status = $statusCode; result = "ERROR" }
        }
    }
}

Write-Host ""
Write-Host "=== Summary ===" -ForegroundColor Cyan
$ok = ($results | Where-Object { $_.result -eq "OK" } | Measure-Object).Count
$auth = ($results | Where-Object { $_.result -eq "AUTH" } | Measure-Object).Count
$error = ($results | Where-Object { $_.result -eq "ERROR" } | Measure-Object).Count
Write-Host "OK: $ok | AUTH: $auth | ERRORS: $error" -ForegroundColor White

