# 🚀 Deployment Checklist - Render + Vercel

## ✅ Pre-Deployment

- [x] Backend database connected (Render PostgreSQL)
- [x] Migrations tested locally
- [x] CORS middleware created
- [ ] Remove hardcoded CORS headers (optional cleanup)
- [ ] Remove debug routes (optional cleanup)

## 📦 Backend Deployment (Render)

### Step 1: Push to GitHub
```bash
cd c:\Project
git add .
git commit -m "Ready for deployment"
git push origin main
```

### Step 2: Deploy on Render
1. Go to **https://render.com** → Dashboard
2. Click **New +** → **Web Service**
3. Connect GitHub repo
4. **Settings:**
   - Name: `laverdad-herald-backend`
   - Environment: `Docker`
   - Branch: `main`
   - Root Directory: `backend`

5. **Build Command:**
   ```
   composer install --no-dev --optimize-autoloader && php artisan config:cache && php artisan route:cache
   ```

6. **Start Command:**
   ```
   php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT
   ```

7. **Environment Variables:**
   ```
   APP_KEY=base64:rcPmrNjfNRXgbOhH2Gl0YsPn6FL+sGiaVTcaXyVoiwA=
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://your-backend.onrender.com
   FRONTEND_URL=https://your-frontend.vercel.app
   ```

8. **Link PostgreSQL:**
   - Render auto-injects: `PGHOST`, `PGPORT`, `PGDATABASE`, `PGUSER`, `PGPASSWORD`
   - Or use existing database connection string

9. Click **Create Web Service**

### Step 3: Get Backend URL
- Copy: `https://laverdad-herald-backend.onrender.com`

---

## 🎨 Frontend Deployment (Vercel)

### Step 1: Update Frontend Config
1. Edit `frontend/.env.production`:
   ```
   VITE_API_BASE_URL=https://laverdad-herald-backend.onrender.com/api
   ```

2. Commit changes:
   ```bash
   git add frontend/.env.production
   git commit -m "Update backend URL"
   git push
   ```

### Step 2: Deploy on Vercel
1. Go to **https://vercel.com** → Dashboard
2. Click **Add New** → **Project**
3. Import GitHub repo
4. **Settings:**
   - Framework: `Vite`
   - Root Directory: `frontend`
   - Build Command: `npm run build`
   - Output Directory: `dist`

5. **Environment Variables:**
   ```
   VITE_API_BASE_URL=https://laverdad-herald-backend.onrender.com/api
   VITE_APP_NAME=La Verdad Herald
   ```

6. Click **Deploy**

### Step 3: Get Frontend URL
- Copy: `https://laverdad-herald.vercel.app`

---

## 🔄 Final Configuration

### Update Backend CORS
1. Go to Render → Your backend service → Environment
2. Update:
   ```
   FRONTEND_URL=https://laverdad-herald.vercel.app
   ```
3. Redeploy backend

### Test Deployment
1. Visit: `https://laverdad-herald.vercel.app`
2. Test login/register
3. Test article creation
4. Check API calls in browser console

---

## 🐛 Troubleshooting

**CORS Errors?**
- Verify `FRONTEND_URL` in Render matches Vercel URL exactly
- Redeploy backend after changing

**Database Connection Failed?**
- Check PostgreSQL is linked in Render
- Verify environment variables are set

**Build Failed?**
- Check Render/Vercel logs
- Verify `composer.json` and `package.json` are correct

**API 500 Errors?**
- Check Render logs
- Run migrations: Render → Shell → `php artisan migrate --force`

---

## 📝 URLs to Save

- **Backend:** https://laverdad-herald-backend.onrender.com
- **Frontend:** https://laverdad-herald.vercel.app
- **Database:** (Render PostgreSQL dashboard)
- **Render Dashboard:** https://dashboard.render.com
- **Vercel Dashboard:** https://vercel.com/dashboard

---

## ✅ Post-Deployment

- [ ] Test all features
- [ ] Create admin account
- [ ] Add sample articles
- [ ] Test email notifications
- [ ] Monitor logs for errors
- [ ] Set up custom domain (optional)
