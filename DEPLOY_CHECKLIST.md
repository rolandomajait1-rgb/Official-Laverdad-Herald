# Amber Backend Deployment Checklist

## ✅ Pre-Deployment (Already Done!)
- [x] Code pushed to GitHub (branch: `amber-backend`)
- [x] Dockerfile created
- [x] Environment variables documented
- [x] API tested locally

## 📋 Deployment Steps

### 1. Render Setup
- [ ] Go to https://render.com
- [ ] Sign up / Login with GitHub
- [ ] Click "New +" → "Web Service"
- [ ] Connect to `Official-Laverdad-Herald` repo

### 2. Configuration
- [ ] Name: `amber-backend`
- [ ] Region: `Singapore`
- [ ] Branch: `amber-backend` ⚠️ IMPORTANT
- [ ] Root Directory: `amber backend`
- [ ] Runtime: `Docker`
- [ ] Instance Type: `Free`

### 3. Environment Variables
Copy these and replace YOUR values:
```
APP_NAME=Amber Backend
APP_ENV=production
APP_KEY=base64:y1mHsFYJdJ+0JAtUeM+DqeTqdqpGH5XUR8uBclRK8Y0=
APP_DEBUG=false
APP_URL=https://amber-backend.onrender.com
DB_CONNECTION=sqlite
DB_PREFIX=amber_
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=YOUR_BREVO_USERNAME
MAIL_PASSWORD=YOUR_BREVO_PASSWORD
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=YOUR_EMAIL@laverdad.edu.ph
MAIL_FROM_NAME=Amber Backend
FRONTEND_URL=https://official-laverdad-herald.vercel.app
```

### 4. Deploy
- [ ] Click "Create Web Service"
- [ ] Wait 5-10 minutes
- [ ] Check logs for errors
- [ ] Note your URL: `https://amber-backend.onrender.com`

### 5. Test Backend
- [ ] Open: `https://amber-backend.onrender.com/api/categories`
- [ ] Should return JSON response
- [ ] Test register endpoint
- [ ] Test login endpoint

### 6. Update Frontend
- [ ] Update Vercel environment variable:
  ```
  VITE_API_BASE_URL=https://amber-backend.onrender.com
  ```
- [ ] Redeploy frontend
- [ ] Test frontend → backend connection

## 🎉 Success Criteria
- [ ] Backend URL is live
- [ ] API endpoints return JSON
- [ ] Frontend can connect to backend
- [ ] Can register/login users
- [ ] Can create/view articles

## 📝 Your Deployment Info

Fill this in after deployment:

```
Backend URL: https://amber-backend.onrender.com
Deployed: [DATE]
Status: [LIVE/TESTING]
Frontend Connected: [YES/NO]
```

## 🆘 If Something Goes Wrong

1. Check Render logs
2. Verify branch is `amber-backend`
3. Verify root directory is `amber backend`
4. Check all environment variables
5. Check Dockerfile exists

---

**Ready? Open https://render.com and start deploying!** 🚀
