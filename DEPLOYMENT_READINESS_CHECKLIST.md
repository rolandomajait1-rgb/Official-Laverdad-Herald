# Deployment Readiness Checklist

## ✅ Code Quality
- ✅ Backend role system cleaned (admin, moderator, user)
- ✅ Frontend role system matches backend
- ✅ No syntax errors in migrations
- ✅ Author is relationship, not role
- ✅ Subscriber table for email notifications only

## ⚠️ Pre-Deployment Requirements

### Backend (Railway)
- ⚠️ **MUST RUN LOCALLY FIRST**: Start MySQL and run `php artisan migrate` to test migrations
- ✅ nixpacks.toml configured
- ✅ Migration auto-runs on deploy
- ⚠️ **UPDATE**: `.env.production` - Replace placeholder database credentials
- ⚠️ **UPDATE**: `.env.production` - Replace `APP_URL` with actual Railway URL
- ⚠️ **VERIFY**: Brevo SMTP credentials are correct
- ⚠️ **ADD**: `FRONTEND_URL` environment variable after Vercel deployment

### Frontend (Vercel)
- ✅ vercel.json configured for SPA routing
- ✅ Build command: `npm run build`
- ✅ Output directory: `dist`
- ⚠️ **UPDATE**: `.env.production` - Replace `VITE_API_BASE_URL` with actual Railway URL

### Database
- ⚠️ **REQUIRED**: Export local database before deployment
  ```bash
  mysqldump -u root "final laverdad herald" > database.sql
  ```
- ⚠️ **REQUIRED**: Import to Railway MySQL after deployment

### CORS Configuration
- ✅ CORS configured to use `FRONTEND_URL` env variable
- ⚠️ **REQUIRED**: Set `FRONTEND_URL` in Railway after getting Vercel URL

## 🔧 Deployment Steps

### 1. Test Locally First
```bash
# Backend
cd backend
php artisan migrate
php artisan serve

# Frontend
cd frontend
npm run build
npm run preview
```

### 2. Deploy Backend to Railway
1. Push code to GitHub
2. Create Railway project
3. Add MySQL database
4. Set environment variables (use Railway's MySQL variables)
5. Deploy
6. Run migrations (auto-runs via nixpacks.toml)
7. Import database dump

### 3. Deploy Frontend to Vercel
1. Create Vercel project
2. Set `VITE_API_BASE_URL` to Railway backend URL
3. Deploy

### 4. Update CORS
1. Add `FRONTEND_URL` to Railway with Vercel URL
2. Redeploy Railway backend

## ⚠️ Critical Issues to Fix Before Deployment

1. **MySQL Not Running Locally**
   - Start XAMPP MySQL service
   - Test migrations: `php artisan migrate`
   - Verify all 21 migrations run successfully

2. **Environment Variables**
   - Update all placeholder values in `.env.production`
   - Get actual Railway MySQL credentials after database creation
   - Get actual Railway and Vercel URLs after deployment

3. **Database Export**
   - Export current local database
   - Have it ready to import to Railway

4. **Test Build Locally**
   - Backend: `composer install --optimize-autoloader --no-dev`
   - Frontend: `npm run build`

## 📋 Post-Deployment Verification

- [ ] Backend health check: `https://your-backend.railway.app/api/health`
- [ ] Frontend loads: `https://your-frontend.vercel.app`
- [ ] Login works
- [ ] Articles display
- [ ] Admin dashboard accessible
- [ ] CORS working (no console errors)
- [ ] Database connected
- [ ] Email notifications work (if configured)

## 🚨 Current Status: NOT READY

**Blockers:**
1. ❌ MySQL not running - cannot test migrations
2. ❌ Migrations not tested locally
3. ❌ Database not exported
4. ❌ Production environment variables not configured

**Next Steps:**
1. Start XAMPP MySQL
2. Run `php artisan migrate` to test all migrations
3. Export database: `mysqldump -u root "final laverdad herald" > database.sql`
4. Update `.env.production` files with actual values
5. Test build locally
6. Then proceed with deployment

## ✅ What's Ready
- Code is clean and consistent
- Deployment configurations are in place
- Role system is properly implemented
- CORS is configured correctly
- Build scripts are ready
