# Deploy Amber Backend to Render (FREE)

## ✅ Your Code is Ready!
- Branch: `amber-backend` 
- Already pushed to: https://github.com/rolandomajait1-rgb/Official-Laverdad-Herald
- Status: Ready to deploy! 🚀

---

## Step 1: Sign Up / Login to Render

1. Go to: https://render.com
2. Click "Get Started for Free"
3. Sign up with GitHub (recommended)
4. Authorize Render to access your GitHub

---

## Step 2: Create New Web Service

1. Click "New +" (top right)
2. Select "Web Service"
3. Click "Connect account" if needed
4. Find your repo: `Official-Laverdad-Herald`
5. Click "Connect"

---

## Step 3: Configure Service

### Basic Settings:
```
Name: amber-backend
Region: Singapore (closest to Philippines)
Branch: amber-backend  ← IMPORTANT!
Root Directory: amber backend
Runtime: Docker
```

### Instance Type:
```
Instance Type: Free
```

---

## Step 4: Add Environment Variables

Click "Advanced" → Scroll to "Environment Variables" → Add these:

```env
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
MAIL_USERNAME=your_brevo_username
MAIL_PASSWORD=your_brevo_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@laverdad.edu.ph
MAIL_FROM_NAME=Amber Backend

FRONTEND_URL=https://official-laverdad-herald.vercel.app
```

**⚠️ IMPORTANT:** Replace these with your real values:
- `MAIL_USERNAME` - Your Brevo SMTP username
- `MAIL_PASSWORD` - Your Brevo SMTP password
- `MAIL_FROM_ADDRESS` - Your email

---

## Step 5: Deploy!

1. Click "Create Web Service"
2. Wait 5-10 minutes for deployment
3. Watch the logs for any errors
4. Once done, you'll see: "Your service is live at https://amber-backend.onrender.com"

---

## Step 6: Test Your Backend

Open your browser and test:

```
https://amber-backend.onrender.com/api/categories
```

Should return JSON (empty array or categories list)

---

## Step 7: Update Frontend

Once deployed, update your frontend:

### A. Update Environment Variable:
```bash
cd frontend
echo "VITE_API_BASE_URL=https://amber-backend.onrender.com" > .env.amber.production
```

### B. Deploy Frontend to Vercel:
1. Go to Vercel dashboard
2. Select your frontend project
3. Go to Settings → Environment Variables
4. Add/Update:
   ```
   VITE_API_BASE_URL=https://amber-backend.onrender.com
   ```
5. Redeploy

---

## Troubleshooting

### Deployment Failed?
- Check logs in Render dashboard
- Make sure branch is `amber-backend`
- Make sure root directory is `amber backend`
- Check Dockerfile exists

### Database Errors?
- SQLite is created automatically
- Check migrations ran: Look for "Running migrations" in logs

### CORS Errors?
- Already configured in `config/cors.php`
- Should work automatically

### 500 Errors?
- Check APP_KEY is set
- Check all environment variables are correct
- Check logs for specific error

---

## Free Tier Limits

Render Free tier includes:
- ✅ 750 hours/month (enough for 1 service 24/7)
- ✅ Automatic HTTPS
- ✅ Custom domains
- ⚠️ Spins down after 15 minutes of inactivity
- ⚠️ Takes ~30 seconds to wake up

**Note:** First request after inactivity will be slow (cold start)

---

## After Deployment

Your backend will be at:
```
https://amber-backend.onrender.com
```

Test endpoints:
- `GET /api/categories` - List categories
- `POST /api/register` - Register user
- `POST /api/login` - Login user
- `GET /api/articles` - List articles

---

## Need Help?

If deployment fails, check:
1. Render logs (in dashboard)
2. Make sure branch is `amber-backend`
3. Make sure Dockerfile exists in `amber backend/` folder
4. All environment variables are set

**Ready to deploy? Go to https://render.com and follow the steps above!** 🚀
