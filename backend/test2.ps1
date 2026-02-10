$urls = @(
    "http://localhost:8000/api/authors",
    "http://localhost:8000/api/tags", 
    "http://localhost:8000/api/logs",
    "http://localhost:8000/api/categories"
)

foreach ($url in $urls) {
    try {
        $response = Invoke-WebRequest -Uri $url -UseBasicParsing
        Write-Host "$url -> $($response.StatusCode) OK"
    } catch {
        Write-Host "$url -> 500 ERROR"
    }
}
