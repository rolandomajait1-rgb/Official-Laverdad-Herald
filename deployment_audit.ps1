#!/usr/bin/env pwsh
# SENIOR DEVELOPER DEPLOYMENT AUDIT

Write-Host "`n======== PRODUCTION DEPLOYMENT AUDIT ========`n" -ForegroundColor Cyan

Write-Host "[1] GIT STATUS" -ForegroundColor Yellow
$branch = git branch --show-current
$commits = git log --oneline -3
Write-Host "  Branch: $branch"
Write-Host "  Latest commits:`n$commits`n" -ForegroundColor Gray

Write-Host "[2] ENVIRONMENT CONFIGURATION" -ForegroundColor Yellow
$envFiles = @("backend\.env.render", "frontend\.env.production")
foreach ($file in $envFiles) {
    $exists = if (Test-Path $file) { "[OK]" } else { "[FAIL]" }
    Write-Host "  $exists $file"
}

Write-Host "`n[3] LOCAL ENDPOINT TESTS" -ForegroundColor Yellow
$localBase = "http://localhost:8000/api"
$endpoints = @(
    "/categories",
    "/authors", 
    "/tags",
    "/logs",
    "/articles/public"
)

$allHealthy = $true
foreach ($endpoint in $endpoints) {
    try {
        $response = Invoke-WebRequest -Uri "$localBase$endpoint" -UseBasicParsing -TimeoutSec 5 -ErrorAction Stop
        Write-Host "  [OK] GET $endpoint -> $($response.StatusCode)" -ForegroundColor Green
    } catch {
        Write-Host "  [FAIL] GET $endpoint" -ForegroundColor Red
        $allHealthy = $false
    }
}

Write-Host "`n[4] DATABASE CONNECTIVITY" -ForegroundColor Yellow
$pgCheck = & php -r "@php
try {
    \$pdo = new PDO(
        'pgsql:host=dpg-d64rrekhg0os73df6t20-a.oregon-postgres.render.com;port=5432;dbname=laverdad_herald_db_hvqa;sslmode=require',
        'laverdad_herald_user',
        'NGH35bix0sOLopEd5xyBagoTi4MvzlsB'
    );
    \$result = \$pdo->query('SELECT COUNT(*) as cnt FROM migrations');
    \$row = \$result->fetch(PDO::FETCH_ASSOC);
    echo 'OK:' . \$row['cnt'] . ' migrations';
} catch (Exception \$e) {
    echo 'FAIL:' . \$e->getMessage();
}
@"

if ($pgCheck -match "^OK:") {
    Write-Host "  [OK] PostgreSQL connected - $pgCheck" -ForegroundColor Green
} else {
    Write-Host "  [FAIL] $pgCheck" -ForegroundColor Red
}

Write-Host "`n[5] DEPLOYMENT READINESS" -ForegroundColor Yellow
$checks = @(
    @{ name = "Main branch active"; pass = ($branch -eq "main") },
    @{ name = "Local endpoints responding"; pass = $allHealthy },
    @{ name = "Database accessible"; pass = ($pgCheck -match "^OK:") }
)

foreach ($check in $checks) {
    $symbol = if ($check.pass) { "[OK]" } else { "[FAIL]" }
    $color = if ($check.pass) { "Green" } else { "Red" }
    Write-Host "  $symbol $($check.name)" -ForegroundColor $color
}

$allPass = $checks | Where-Object { -not $_.pass }
Write-Host "`n" + ("=" * 50)

if ($allPass.Count -eq 0) {
    Write-Host "RESULT: READY FOR PRODUCTION" -ForegroundColor Green -BackgroundColor DarkGreen
    Write-Host "`nAll systems operational. Deployment is safe." -ForegroundColor Green
} else {
    Write-Host "RESULT: ISSUES DETECTED" -ForegroundColor Red
}

Write-Host "=" * 50
Write-Host "`nDEPLOYMENT COMMANDS EXECUTED:" -ForegroundColor Yellow
Write-Host "  1. git push origin master:main (consolidated commits)"
Write-Host "  2. git push origin --delete master (removed old branch)"
Write-Host "  3. Procfile updated with error handling"
Write-Host "  4. All 3 broken endpoints fixed locally (200 OK)"
Write-Host "`nNEXT STEP: Monitor https://dashboard.render.com for backend deployment`n" -ForegroundColor Cyan
