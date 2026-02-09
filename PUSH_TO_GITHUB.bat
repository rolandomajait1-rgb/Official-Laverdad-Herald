@echo off
echo ========================================
echo Push to GitHub
echo ========================================
echo.

cd /d "%~dp0"

echo Step 1: Initialize Git (if not already)
git init 2>nul

echo.
echo Step 2: Add all files
git add .

echo.
echo Step 3: Commit
set /p commit_msg="Enter commit message (or press Enter for 'Initial commit'): "
if "%commit_msg%"=="" set commit_msg=Initial commit
git commit -m "%commit_msg%"

echo.
echo Step 4: Add remote repository
echo.
echo Go to GitHub and create a new repository
echo Then copy the repository URL (e.g., https://github.com/username/repo.git)
echo.
set /p repo_url="Paste your GitHub repository URL: "

git remote remove origin 2>nul
git remote add origin %repo_url%

echo.
echo Step 5: Push to GitHub
git branch -M main
git push -u origin main

echo.
echo ========================================
echo Done! Your code is now on GitHub
echo ========================================
pause
