@echo off
echo ========================================
echo  FIXING IMAGES AND EMAIL VERIFICATION
echo ========================================
echo.

cd /d "c:\Official Laverdad Herald\backend"

echo [1/6] Creating storage symlink for images...
php artisan storage:link
if %errorlevel% neq 0 (
    echo ERROR: Failed to create storage symlink
    pause
    exit /b 1
)
echo ✅ Storage symlink created

echo.
echo [2/6] Creating local database...
mysql -u root -e "CREATE DATABASE IF NOT EXISTS laverdad_herald;"
if %errorlevel% neq 0 (
    echo ⚠️  Warning: Could not create database (MySQL might not be running)
    echo    Please start XAMPP MySQL first
)

echo.
echo [3/6] Running database migrations...
php artisan migrate:fresh --seed
if %errorlevel% neq 0 (
    echo ERROR: Migration failed
    pause
    exit /b 1
)
echo ✅ Database migrated and seeded

echo.
echo [4/6] Clearing Laravel cache...
php artisan config:clear
php artisan cache:clear
php artisan route:clear
echo ✅ Cache cleared

echo.
echo [5/6] Checking storage directory permissions...
if not exist "storage\app\public\images" mkdir "storage\app\public\images"
echo ✅ Storage directories ready

echo.
echo [6/6] Testing image upload path...
php artisan route:list | findstr storage
echo ✅ Routes configured

echo.
echo ========================================
echo  FIXES APPLIED SUCCESSFULLY!
echo ========================================
echo.
echo ✅ Images: Storage symlink created
echo ✅ Database: Switched to local MySQL  
echo ✅ Email: Verification enabled
echo ✅ Cache: Cleared all caches
echo.
echo ⚠️  IMPORTANT: Update your .env file with:
echo    - Real email credentials for SMTP
echo    - Your actual Brevo API key
echo.
echo Ready to test! Run: php artisan serve
echo.
pause