@echo off
echo ========================================
echo Testing Registration API
echo ========================================
echo.

echo Testing with valid data...
curl -X POST http://localhost:8000/api/register ^
  -H "Content-Type: application/json" ^
  -d "{\"name\":\"Test User\",\"email\":\"test@example.com\",\"password\":\"Password123\",\"password_confirmation\":\"Password123\"}"

echo.
echo.
echo ========================================
echo Test Complete!
echo ========================================
echo.
echo Expected: 201 status with success message
echo.
pause
