# Deployment Checklist for Production

## Pre-Deployment

### 1. Environment Variables
Ensure these are set in Render.com dashboard:

```env
APP_NAME="Official Laverdad Herald"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://official-laverdad-herald.onrender.com
APP_KEY=[your-app-key]

DB_CONNECTION=pgsql
DB_HOST=[render-postgres-host]
DB_PORT=5432
DB_DATABASE=[database-name]
DB_USERNAME=[database-user]
DB_PASSWORD=[database-password]
DB_SSLMODE=require

MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=[your-brevo-username]
MAIL_PASSWORD=[your-brevo-password]
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=rolandomajait1@gmail.com
MAIL_FROM_NAME="Official La Verdad Herald"

FRONTEND_URL=https://official-laverdad-herald.vercel.app

FILESYSTEM_DISK=public

SESSION_DRIVER=file
CACHE_DRIVER=file
QUEUE_CONNECTION=database
```

### 2. Build Commands (Render.com)
```bash
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

### 3. Start Command (Render.com)
```bash
php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT
```

## Post-Deployment Commands

### Run These After Each Deployment

```bash
# 1. Create storage symlink (if not exists)
php artisan storage:link

# 2. Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 3. Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Run migrations
php artisan migrate --force

# 5. Set permissions (if needed)
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

## Verification Steps

### 1. Check Storage Symlink
```bash
ls -la public/storage
# Should show: storage -> ../storage/app/public
```

### 2. Test Image Upload
1. Login as admin
2. Create a new article with an image
3. Check database: `featured_image` column should have path
4. Visit article: image should display
5. Check URL: should be HTTPS

### 3. Test Authentication
1. Register new user
2. Check email for verification link
3. Verify email
4. Login successfully
5. Test password reset

### 4. Check Logs
```bash
tail -f storage/logs/laravel.log
```

## Common Issues

### Issue: Storage Symlink Missing
**Solution:**
```bash
php artisan storage:link
```

### Issue: Permission Denied
**Solution:**
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### Issue: Config Cached with Wrong Values
**Solution:**
```bash
php artisan config:clear
php artisan config:cache
```

### Issue: Images Not Loading
**Solution:**
1. Check APP_URL is HTTPS
2. Verify storage symlink exists
3. Check file permissions
4. Clear browser cache

## Frontend Deployment (Vercel)

### Environment Variables
```env
VITE_API_BASE_URL=https://official-laverdad-herald.onrender.com
```

### Build Settings
- Framework Preset: Vite
- Build Command: `npm run build`
- Output Directory: `dist`
- Install Command: `npm install`

## Health Checks

### Backend Health
```bash
# Test API
curl https://official-laverdad-herald.onrender.com/api/articles/public

# Test storage
curl -I https://official-laverdad-herald.onrender.com/storage/articles/test.jpg
```

### Frontend Health
```bash
# Test frontend
curl https://official-laverdad-herald.vercel.app

# Check API connection
# Open browser console and check for CORS errors
```

## Monitoring

### What to Monitor
1. Laravel logs: `storage/logs/laravel.log`
2. Error rates in Render dashboard
3. Response times
4. Database connections
5. Email delivery rates

### Key Metrics
- API response time < 500ms
- Error rate < 1%
- Image upload success rate > 95%
- Email delivery rate > 90%

## Rollback Plan

If deployment fails:
1. Revert to previous commit
2. Redeploy from Render dashboard
3. Check logs for errors
4. Verify environment variables
5. Test critical features

## Success Criteria

✅ All environment variables set correctly  
✅ Storage symlink created  
✅ Migrations run successfully  
✅ Images upload and display correctly  
✅ Authentication works (login, register, verify)  
✅ Email sending works  
✅ No errors in logs  
✅ Frontend connects to backend  
✅ HTTPS enforced everywhere  

## Post-Deployment Testing

### Critical Features to Test
1. User registration and email verification
2. User login
3. Password reset
4. Article creation with image upload
5. Article viewing
6. Image display in articles
7. Admin dashboard access
8. Moderator functions

### Performance Testing
1. Page load times
2. API response times
3. Image load times
4. Database query performance

## Maintenance

### Daily
- Check error logs
- Monitor disk space
- Verify email delivery

### Weekly
- Review performance metrics
- Check for failed jobs
- Clean up old logs

### Monthly
- Update dependencies
- Security audit
- Backup database

## Support Contacts

- Backend: Render.com support
- Frontend: Vercel support
- Email: Brevo support
- Database: Render Postgres support

## Notes

- Always test in staging before production
- Keep backups of database
- Document any manual changes
- Monitor logs after deployment
- Have rollback plan ready

Deployment checklist complete! ✅
