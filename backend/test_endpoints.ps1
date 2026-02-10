$base='http://localhost:8000/api'

Write-Host 'Logging in to get token...'
$loginResp = Invoke-WebRequest -Uri "$base/login" -Method POST -Body '{"email":"final@test.com","password":"Pass123"}' -ContentType 'application/json' -UseBasicParsing
$loginJson = $loginResp.Content | ConvertFrom-Json
$token = $loginJson.token
Write-Host "Token obtained: " $token

$headers = @{'Authorization' = "Bearer $token"}
$uris = @("$base/authors","$base/tags","$base/logs","$base/articles","$base/categories")

foreach ($u in $uris) {
    try {
        $r = Invoke-WebRequest -Uri $u -Headers $headers -UseBasicParsing
        Write-Host ($u + ' -> ' + $r.StatusCode.ToString()) -ForegroundColor Green
    } catch {
        if ($_.Exception.Response) {
            Write-Host ($u + ' -> ERROR ' + $_.Exception.Response.StatusCode.Value__.ToString()) -ForegroundColor Red
        } else {
            Write-Host ($u + ' -> ERROR (no response)') -ForegroundColor Red
        }
    }
}
