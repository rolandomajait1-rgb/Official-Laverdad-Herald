@echo off
echo ========================================
echo XAMPP MySQL Fix Script
echo ========================================
echo.

echo Step 1: Stopping any MySQL processes...
taskkill /F /IM mysqld.exe 2>nul
timeout /t 2 >nul

echo Step 2: Checking port 3306...
netstat -ano | findstr :3306
echo.

echo Step 3: Backup and clean MySQL data...
cd C:\xampp\mysql\data
if exist ibdata1 (
    echo Backing up ibdata1...
    copy ibdata1 ibdata1.backup >nul
    del ibdata1
)
if exist ib_logfile0 del ib_logfile0
if exist ib_logfile1 del ib_logfile1

echo Step 4: Restore from backup...
if exist C:\xampp\mysql\backup\ibdata1 (
    copy C:\xampp\mysql\backup\ibdata1 . >nul
    copy C:\xampp\mysql\backup\ib_logfile0 . >nul
    copy C:\xampp\mysql\backup\ib_logfile1 . >nul
    echo Backup restored!
) else (
    echo No backup found, MySQL will recreate files.
)

echo.
echo ========================================
echo DONE! Now try starting MySQL in XAMPP
echo ========================================
pause
