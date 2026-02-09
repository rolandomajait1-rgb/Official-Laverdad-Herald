@echo off
echo ========================================
echo  Starting MySQL and Laravel Server
echo ========================================
echo.

echo Step 1: Start XAMPP Control Panel
echo Please start MySQL from XAMPP manually
echo.
start "" "C:\xampp\xampp-control.exe"
echo.
echo Waiting 10 seconds for MySQL to start...
timeout /t 10 /nobreak >nul
echo.

echo Step 2: Starting Laravel Server
cd backend
start "Laravel Server" cmd /k "php artisan serve"
echo.

echo ========================================
echo  Server started at: http://localhost:8000
echo  Now test in Postman!
echo ========================================
pause
