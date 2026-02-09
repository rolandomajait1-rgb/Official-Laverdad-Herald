@echo off
echo ========================================
echo MySQL XAMPP Fix Script
echo ========================================
echo.

:: Check if running as admin
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo Please run as Administrator!
    echo Right-click this file and select "Run as administrator"
    pause
    exit /b 1
)

echo Step 1: Stopping MySQL if running...
taskkill /F /IM mysqld.exe 2>nul
timeout /t 2 >nul

echo Step 2: Backing up current data folder...
if exist "C:\xampp\mysql\data" (
    if not exist "C:\xampp\mysql\data_backup_%date:~-4,4%%date:~-10,2%%date:~-7,2%" (
        xcopy "C:\xampp\mysql\data" "C:\xampp\mysql\data_backup_%date:~-4,4%%date:~-10,2%%date:~-7,2%\" /E /I /H /Y
        echo Backup created successfully
    )
)

echo Step 3: Restoring from backup folder...
if exist "C:\xampp\mysql\backup" (
    if exist "C:\xampp\mysql\data" (
        rename "C:\xampp\mysql\data" "data_old_%date:~-4,4%%date:~-10,2%%date:~-7,2%"
    )
    xcopy "C:\xampp\mysql\backup" "C:\xampp\mysql\data\" /E /I /H /Y
    echo Data restored from backup
) else (
    echo WARNING: Backup folder not found. Trying to fix corrupted files...
    
    if exist "C:\xampp\mysql\data\ibdata1" del "C:\xampp\mysql\data\ibdata1"
    if exist "C:\xampp\mysql\data\ib_logfile0" del "C:\xampp\mysql\data\ib_logfile0"
    if exist "C:\xampp\mysql\data\ib_logfile1" del "C:\xampp\mysql\data\ib_logfile1"
    if exist "C:\xampp\mysql\data\aria_log_control" del "C:\xampp\mysql\data\aria_log_control"
)

echo.
echo ========================================
echo Fix completed!
echo Now start MySQL from XAMPP Control Panel
echo ========================================
pause
