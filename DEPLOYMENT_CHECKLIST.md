# 🚀 Deployment Checklist

## ✅ Pre-Deployment

- [ ] Test locally (MySQL + Laravel + React working)
- [ ] All migrations tested
- [ ] `.env.production` files configured
- [ ] Git repository created and pushed

## 📦 Backend (Railway)

### Setup
- [ ] Create Railway account
- [ ] New Project → Deploy from GitHub
- [ ] Add MySQL database
- [ ] Configure environment variables

### Environment Variables
```env
APP_NAME=La Verdad Herald
APP_ENV=production
APP_KEY=base64:rcPmrNjfNRXgbOhH2Gl0YsPn6FL+sGiaVTcaXyVoiwA=
APP_DEBUG=false
APP_URL=https://your-backend.railway.app

DB_CONNECTION=mysql
DB_HOST=${{MYSQLHOST}}
DB_PORT=${{MYSQLPORT}}
DB_DATABASE=${{MYSQLDATABASE}}
DB_USERNAME=${{MYSQLUSER}}
DB_PASSWORD=${{MYSQLPASSWORD}}

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=rolandomajait1@gmail.com
MAIL_PASSWORD=bfd89c8fa2b60e5d79f647d3817c7e384e186eef735506bf63c60fe4e0ccc32d-v98D9ajcvPfvrTqZ
MAIL_FROM_ADDRESS=rolandomajait1@gmail.com
MAIL_FROM_NAME=LA_VERDAD_HERALD

FRONTEND_URL=https://your-frontend.vercel.app
```

### Verify
- [ ] Backend URL accessible
- [ ] Database connected
- [ ] Migrations ran successfully

## 🎨 Frontend (Vercel)

### Setup
- [ ] Create Vercel account
- [ ] New Project → Import Git Repository
- [ ] Framework: Vite
- [ ] Root Directory: `frontend`

### Environment Variables
```env
VITE_API_BASE_URL=https://your-backend.railway.app/api
VITE_APP_NAME=La Verdad Herald
```

### Build Settings
- Build Command: `npm run build`
- Output Directory: `dist`
- Install Command: `npm install`

### Verify
- [ ] Frontend URL accessible
- [ ] Can connect to backend API
- [ ] Login/Register working

## 🔄 Post-Deployment

- [ ] Update Railway `FRONTEND_URL` with actual Vercel URL
- [ ] Redeploy backend
- [ ] Test CORS (login, API calls)
- [ ] Import production database (if needed)
- [ ] Test all features end-to-end

## 📝 URLs to Save

- Backend: `https://your-backend.railway.app`
- Frontend: `https://your-frontend.vercel.app`
- Railway Dashboard: `https://railway.app/dashboard`
- Vercel Dashboard: `https://vercel.com/dashboard`

## 🆘 Troubleshooting

**CORS errors?**
- Check `FRONTEND_URL` in Railway matches Vercel URL exactly
- Redeploy backend after changing

**Database not connecting?**
- Verify Railway MySQL variables are set correctly
- Check `DB_HOST=${{MYSQLHOST}}` format

**Build failing?**
- Check `nixpacks.toml` and `Procfile` in backend
- Verify `vercel.json` in frontend

**API 500 errors?**
- Check Railway logs
- Verify `APP_KEY` is set
- Run migrations: `php artisan migrate --force`
