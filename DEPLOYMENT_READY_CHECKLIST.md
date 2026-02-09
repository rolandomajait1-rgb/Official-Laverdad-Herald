# 🚀 DEPLOYMENT CHECKLIST - RENDER + VERCEL

## ✅ PRE-DEPLOYMENT FIXES APPLIED

- [x] Added PostgreSQL support to Dockerfile (pdo_pgsql, libpq-dev)
- [x] Fixed port configuration to use Render's $PORT variable
- [x] Removed hardcoded port 8080
- [x] Fixed duplicate route names in api.php
- [x] CORS configured for production
- [x] Axios baseURL fixed in frontend

---

## 📦 BACKEND DEPLOYMENT (RENDER)

### Step 1: Create PostgreSQL Database
1. Go to **Render Dashboard** → **New** → **PostgreSQL**
2. Name: `laverdad-herald-db`
3. Click **Create Database**
4. **Copy connection details** (you'll need these)

### Step 2: Deploy Backend
1. **New** → **Web Service**
2. Connect GitHub: `Official-Laverdad-Herald`
3. **Settings:**
   - Name: `laverdad-herald-backend`
   - Environment: **Docker**
   - Branch: `main`
   - Root Directory: `backend`

4. **Environment Variables** (Add these):
```
APP_NAME=La Verdad Herald
APP_ENV=production
APP_KEY=base64:rcPmrNjfNRXgbOhH2Gl0YsPn6FL+sGiaVTcaXyVoiwA=
APP_DEBUG=false
APP_URL=https://laverdad-herald-backend.onrender.com
DB_CONNECTION=pgsql
FRONTEND_URL=https://your-frontend.vercel.app
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
SESSION_SECURE_COOKIE=true
```

5. **Link Database:**
   - Click **Environment** tab
   - **Add Database** → Select your PostgreSQL database
   - Render auto-injects: PGHOST, PGPORT, PGDATABASE, PGUSER, PGPASSWORD

6. **Deploy**

7. **Copy Backend URL:** `https://laverdad-herald-backend.onrender.com`

---

## 🎨 FRONTEND DEPLOYMENT (VERCEL)

### Step 1: Update Frontend Config
1. Update `frontend/.env.production`:
```
VITE_API_BASE_URL=https://laverdad-herald-backend.onrender.com/api
VITE_APP_NAME=La Verdad Herald
```

2. Commit and push:
```bash
git add frontend/.env.production
git commit -m "Update production API URL"
git push origin main
```

### Step 2: Deploy on Vercel
1. Go to **vercel.com** → **New Project**
2. Import: `Official-Laverdad-Herald`
3. **Settings:**
   - Framework Preset: **Vite**
   - Root Directory: `frontend`
   - Build Command: `npm run build`
   - Output Directory: `dist`

4. **Environment Variables:**
```
VITE_API_BASE_URL=https://laverdad-herald-backend.onrender.com/api
VITE_APP_NAME=La Verdad Herald
```

5. **Deploy**

6. **Copy Frontend URL:** `https://laverdad-herald.vercel.app`

---

## 🔄 FINAL CONFIGURATION

### Update Backend CORS
1. Go to **Render** → Your backend service → **Environment**
2. Update `FRONTEND_URL` with your actual Vercel URL:
```
FRONTEND_URL=https://laverdad-herald.vercel.app
```
3. **Manual Deploy** → **Deploy latest commit**

---

## 🧪 TESTING CHECKLIST

After deployment, test these:

- [ ] Visit frontend URL - loads without errors
- [ ] Open browser console - no CORS errors
- [ ] Register new account - works
- [ ] Login - works
- [ ] View articles - loads data
- [ ] Create article (admin) - saves to database
- [ ] Check backend logs in Render - no errors

---

## 🐛 TROUBLESHOOTING

### "Connection refused" error
- ✅ **FIXED:** Added PostgreSQL extensions to Dockerfile
- ✅ **FIXED:** Database linked in Render

### "Port not detected" error
- ✅ **FIXED:** Changed from port 8080 to $PORT variable

### "Route already assigned" error
- ✅ **FIXED:** Removed duplicate users route

### CORS errors
- Check `FRONTEND_URL` matches Vercel URL exactly
- Redeploy backend after changing

### 500 errors
- Check Render logs
- Verify all environment variables are set
- Check database connection

---

## 📝 ENVIRONMENT VARIABLES SUMMARY

### Backend (Render)
```
APP_NAME=La Verdad Herald
APP_ENV=production
APP_KEY=base64:rcPmrNjfNRXgbOhH2Gl0YsPn6FL+sGiaVTcaXyVoiwA=
APP_DEBUG=false
APP_URL=https://laverdad-herald-backend.onrender.com
DB_CONNECTION=pgsql
FRONTEND_URL=https://laverdad-herald.vercel.app
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
SESSION_SECURE_COOKIE=true
```

### Frontend (Vercel)
```
VITE_API_BASE_URL=https://laverdad-herald-backend.onrender.com/api
VITE_APP_NAME=La Verdad Herald
```

---

## ✅ POST-DEPLOYMENT

- [ ] Create admin account via backend shell
- [ ] Test all features
- [ ] Monitor logs for 24 hours
- [ ] Set up custom domain (optional)
