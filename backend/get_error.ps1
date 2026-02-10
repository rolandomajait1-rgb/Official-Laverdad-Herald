# Capture the actual error from /api/authors endpoint
$uri = "http://localhost:8000/api/authors"
try {
    $response = Invoke-WebRequest -Uri $uri -UseBasicParsing -ErrorAction Stop
    Write-Host "Status: $($response.StatusCode)"
    Write-Host "Body:"
    Write-Host $response.Content
} catch {
    $statusCode = $_.Exception.Response.StatusCode.Value__
    Write-Host "Status: $statusCode"
    
    # Try to read response body
    try {
        $stream = $_.Exception.Response.GetResponseStream()
        $reader = New-Object System.IO.StreamReader($stream)
        $body = $reader.ReadToEnd()
        Write-Host "Error Body:"
        Write-Host $body
    } catch {
        Write-Host "Could not read response body"
    }
}
