@echo off
echo ========================================
echo  La Verdad Herald - Test Suite
echo ========================================
echo.

cd backend
echo Running Backend Tests...
echo.
php artisan test
echo.

if %ERRORLEVEL% EQU 0 (
    echo ========================================
    echo  All Tests Passed!
    echo ========================================
) else (
    echo ========================================
    echo  Some Tests Failed!
    echo ========================================
)

pause
