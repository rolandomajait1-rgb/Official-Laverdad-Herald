@echo off
echo ========================================
echo La Verdad Herald - Deployment Helper
echo ========================================
echo.
echo This will help you deploy to Railway + Vercel
echo.
echo STEP 1: BACKEND (Railway)
echo ========================
echo 1. Go to https://railway.app
echo 2. New Project ^> Deploy from GitHub
echo 3. Select your backend repo
echo 4. Add MySQL database
echo 5. Copy environment variables from backend\.env.production
echo 6. Update these Railway variables:
echo    - APP_URL=https://your-backend.railway.app
echo    - DB_HOST=${{MYSQLHOST}}
echo    - DB_PORT=${{MYSQLPORT}}
echo    - DB_DATABASE=${{MYSQLDATABASE}}
echo    - DB_USERNAME=${{MYSQLUSER}}
echo    - DB_PASSWORD=${{MYSQLPASSWORD}}
echo    - FRONTEND_URL=https://your-frontend.vercel.app
echo.
pause
echo.
echo STEP 2: FRONTEND (Vercel)
echo =========================
echo 1. Go to https://vercel.com
echo 2. New Project ^> Import Git Repository
echo 3. Select your frontend repo
echo 4. Framework Preset: Vite
echo 5. Root Directory: frontend
echo 6. Add environment variables:
echo    - VITE_API_BASE_URL=https://your-backend.railway.app/api
echo    - VITE_APP_NAME=La Verdad Herald
echo.
pause
echo.
echo STEP 3: UPDATE CORS
echo ===================
echo 1. Copy your Vercel URL
echo 2. Update Railway FRONTEND_URL variable
echo 3. Redeploy backend on Railway
echo.
pause
echo.
echo ========================================
echo Deployment guide complete!
echo Check RAILWAY_VERCEL_DEPLOYMENT.md for details
echo ========================================
pause
