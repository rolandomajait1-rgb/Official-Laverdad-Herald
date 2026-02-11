# Render + Vercel Deployment with Brevo Email

## Architecture
- **Backend**: Render (Laravel API)
- **Frontend**: Vercel (React)
- **Database**: Render PostgreSQL
- **Email**: Brevo SMTP

## Part 1: Setup Brevo Email (Do This First)

### 1. Create Brevo Account
1. Go to: https://www.brevo.com
2. Sign up (FREE - 300 emails/day)
3. Verify your email

### 2. Get SMTP Credentials
1. Login to Brevo
2. Click your name → "SMTP & API"
3. Click "SMTP" tab
4. Click "Create a new SMTP key"
5. Name: "La Verdad Herald"
6. Copy the SMTP key (starts with `xsmtpsib-`)

### 3. Verify Sender Email
1. Go to "Senders" in left menu
2. Click "Add a sender"
3. Enter: `noreply@laverdad.edu.ph`
4. Check inbox and verify

## Part 2: Deploy Backend to Render

### 1. Create PostgreSQL Database
1. Go to: https://dashboard.render.com
2. Click "New +" → "PostgreSQL"
3. Name: `laverdad-herald-db`
4. Region: Oregon (US West)
5. Plan: Free
6. Click "Create Database"
7. Copy the connection details:
   - **Internal Database URL** (for Render services)
   - **External Database URL** (for local testing)

### 2. Create Web Service
1. Click "New +" → "Web Service"
2. Connect your GitHub repository
3. Configure:
   - **Name**: `laverdad-herald-api`
   - **Region**: Oregon (US West)
   - **Branch**: `main`
   - **Root Directory**: `backend`
   - **Runtime**: PHP
   - **Build Command**: 
     ```bash
     composer install --no-dev --optimize-autoloader && php artisan config:cache && php artisan route:cache && php artisan view:cache
     ```
   - **Start Command**: 
     ```bash
     php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT
     ```
   - **Plan**: Free

### 3. Add Environment Variables
Click "Environment" tab and add:

```
APP_NAME=La Verdad Herald
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:rcPmrNjfNRXgbOhH2Gl0YsPn6FL+sGiaVTcaXyVoiwA=
APP_URL=https://laverdad-herald-api.onrender.com

DB_CONNECTION=pgsql
DB_HOST=dpg-xxxxx.oregon-postgres.render.com
DB_PORT=5432
DB_DATABASE=laverdad_herald_db
DB_USERNAME=laverdad_herald_user
DB_PASSWORD=your_postgres_password
DB_SSLMODE=require

MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your_brevo_email@example.com
MAIL_PASSWORD=xsmtpsib-your_smtp_key_here
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@laverdad.edu.ph
MAIL_FROM_NAME=La Verdad Herald

FRONTEND_URL=https://laverdad.edu.ph

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
BCRYPT_ROUNDS=12
```

**Replace:**
- Database credentials from Step 1
- Brevo credentials from Part 1
- `APP_URL` with your actual Render URL

### 4. Deploy
1. Click "Create Web Service"
2. Wait for deployment (5-10 minutes)
3. Check logs for errors

## Part 3: Deploy Frontend to Vercel

### 1. Create Vercel Project
1. Go to: https://vercel.com
2. Click "Add New" → "Project"
3. Import your GitHub repository
4. Configure:
   - **Framework Preset**: Vite
   - **Root Directory**: `frontend`
   - **Build Command**: `npm run build`
   - **Output Directory**: `dist`

### 2. Add Environment Variables
Click "Environment Variables" and add:

```
VITE_API_URL=https://laverdad-herald-api.onrender.com
```

### 3. Deploy
1. Click "Deploy"
2. Wait for deployment (2-3 minutes)
3. Get your Vercel URL

### 4. Add Custom Domain (Optional)
1. Go to Project Settings → Domains
2. Add: `laverdad.edu.ph`
3. Follow DNS configuration instructions

## Part 4: Update CORS for Production

Update `backend/app/Http/Helpers/CorsHelper.php`:

```php
$allowedOrigins = [
    'https://laverdad.edu.ph',
    'https://your-vercel-app.vercel.app',
    'http://localhost:5173'
];
```

Commit and push to trigger Render redeploy.

## Part 5: Test Email Verification

1. Register new user with `@laverdad.edu.ph` email
2. Check email inbox for verification link
3. Click verification link
4. Try to login

## Monitoring

### Render Dashboard
- View logs: Render Dashboard → Your Service → Logs
- Check metrics: CPU, Memory usage
- Monitor deployments

### Brevo Dashboard
- Email statistics: Brevo → Statistics
- Daily limit: 300 emails/day
- Delivery rate, bounces

### Vercel Dashboard
- Deployment logs
- Analytics
- Performance metrics

## Troubleshooting

### Backend Issues

**Database connection failed:**
- Verify DB credentials in Render environment
- Check `DB_SSLMODE=require` is set
- Test connection from Render shell

**Email not sending:**
- Verify Brevo SMTP key is correct
- Check sender email is verified in Brevo
- View Render logs for email errors

**CORS errors:**
- Update `CorsHelper.php` with correct domains
- Redeploy backend

### Frontend Issues

**API calls failing:**
- Verify `VITE_API_URL` in Vercel
- Check backend is running on Render
- Test API endpoint directly

**Build failing:**
- Check Node version compatibility
- Verify all dependencies in `package.json`
- Review Vercel build logs

## Cost Breakdown

- **Render Free Tier**: 
  - 750 hours/month
  - Sleeps after 15 min inactivity
  - PostgreSQL 1GB storage

- **Vercel Free Tier**:
  - 100GB bandwidth/month
  - Unlimited deployments
  - Custom domains

- **Brevo Free Tier**:
  - 300 emails/day
  - Unlimited contacts

**Total Cost: $0/month** ✅

## Upgrade Options

If you need more:
- **Render**: $7/month (no sleep, more resources)
- **Vercel**: $20/month (more bandwidth, analytics)
- **Brevo**: $25/month (20,000 emails/month)

## Support

- Render: https://render.com/docs
- Vercel: https://vercel.com/docs
- Brevo: https://help.brevo.com
