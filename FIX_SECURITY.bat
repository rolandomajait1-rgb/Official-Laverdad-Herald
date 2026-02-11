@echo off
echo ========================================
echo  FIXING SECURITY VULNERABILITIES
echo ========================================
echo.

echo [1/3] Updating Frontend Dependencies...
cd frontend
call npm install axios@1.13.5
echo.

echo [2/3] Updating Backend Composer Dependencies...
cd ..\backend
call composer update
echo.

echo [3/3] Clearing Caches...
call php artisan config:clear
call php artisan cache:clear
echo.

echo ========================================
echo  SECURITY FIXES APPLIED!
echo ========================================
echo.
echo Fixed Issues:
echo - axios upgraded to 1.13.5 (High severity)
echo - Composer dependencies updated
echo.
echo Note: Some vulnerabilities are in vendor packages
echo and cannot be fixed without Laravel framework updates.
echo.
pause
