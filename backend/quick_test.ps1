$endpoints = @(
    'http://localhost:8000/api/authors',
    'http://localhost:8000/api/tags',
    'http://localhost:8000/api/logs',
    'http://localhost:8000/api/articles/public',
    'http://localhost:8000/api/categories'
)

foreach ($url in $endpoints) {
    try {
        $resp = Invoke-WebRequest -Uri $url -UseBasicParsing
        Write-Host "$url -> $($resp.StatusCode)"
    } catch {
        Write-Host "$url -> ERROR $($_.Exception.Response.StatusCode.Value__)"
    }
}
