$baseUrl = "https://official-laverdad-herald.onrender.com/api"

Write-Host "`nQUICK API TEST (5 endpoints)`n" -ForegroundColor Cyan

$tests = @(
    @{Name="Categories"; Url="$baseUrl/categories"}
    @{Name="Articles"; Url="$baseUrl/articles/public"}
    @{Name="Latest"; Url="$baseUrl/latest-articles"}
    @{Name="Authors"; Url="$baseUrl/authors"}
    @{Name="Search"; Url="$baseUrl/articles/search?q=test"}
)

$pass = 0
$fail = 0

foreach ($test in $tests) {
    Write-Host "$($test.Name)..." -NoNewline
    try {
        Invoke-RestMethod -Uri $test.Url -Method GET -TimeoutSec 10 | Out-Null
        Write-Host " OK" -ForegroundColor Green
        $pass++
    } catch {
        Write-Host " FAIL" -ForegroundColor Red
        $fail++
    }
}

Write-Host "`nResult: $pass/$($tests.Count) passed" -ForegroundColor $(if($fail -eq 0){"Green"}else{"Yellow"})
