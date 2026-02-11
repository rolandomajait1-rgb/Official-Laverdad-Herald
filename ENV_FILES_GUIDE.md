# 🔐 Environment Files Guide

## 📁 Your Current Setup

You have **6 environment files** across backend and frontend:

### Backend (Laravel) - 4 files
1. **`.env`** - Currently active (local development)
2. **`.env.example`** - Template for new developers
3. **`.env.production`** - Minimal production overrides
4. **`.env.render`** - Render.com deployment config

### Frontend (React) - 2 files
1. **`.env`** - Local development
2. **`.env.production`** - Production deployment

---

## 🎯 Which File Does What?

### Backend Files

#### 1️⃣ `.env` (CURRENT - Local Dev)
**Purpose:** Your daily development work on localhost
**Database:** PostgreSQL on Render (dpg-d64rrekhg0os73df6t20)
**Frontend URL:** http://localhost:5173
**Status:** ✅ Active when you run `START_PROJECT.bat`

**⚠️ ISSUE:** You're using production database in local dev!

#### 2️⃣ `.env.example` (Template)
**Purpose:** Template for team members or fresh installs
**Database:** MySQL (placeholder)
**Status:** Not used directly, just a reference

#### 3️⃣ `.env.production` (Minimal)
**Purpose:** Production overrides (very minimal)
**Database:** PostgreSQL
**Status:** Used during deployment

#### 4️⃣ `.env.render` (Render Platform)
**Purpose:** Specific config for Render.com hosting
**Database:** Uses Render's auto-injected variables
**Frontend URL:** https://your-frontend.vercel.app
**Status:** Used when deployed to Render

### Frontend Files

#### 1️⃣ `.env` (Local Dev)
**API URL:** http://localhost:8000/api
**Status:** ✅ Active during local development

#### 2️⃣ `.env.production` (Production)
**API URL:** https://official-laverdad-herald.onrender.com/api
**Status:** Used when deploying to Vercel

---

## 🚨 Current Problems

### Problem 1: Local Dev Using Production Database
Your `backend/.env` points to production Render database. This is risky!

**Solution:** Use local MySQL from XAMPP instead.

### Problem 2: Missing APP_NAME
Your `backend/.env` has empty `APP_NAME=`

**Solution:** Set it to "La Verdad Herald"

---

## ✅ Recommended Setup

### For Local Development (What You Should Use)

**Backend `.env`:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laverdad_herald
DB_USERNAME=root
DB_PASSWORD=
FRONTEND_URL=http://localhost:5173
```

**Frontend `.env`:**
```env
VITE_API_BASE_URL=http://localhost:8000/api
```

### For Production (Railway/Render + Vercel)

**Backend:** Use `.env.production` or `.env.render`
**Frontend:** Use `.env.production`

---

## 🎬 Quick Actions

### Switch to Local MySQL Database
1. Make sure XAMPP MySQL is running
2. Update `backend/.env`:
   - Change `DB_CONNECTION=mysql`
   - Change `DB_HOST=127.0.0.1`
   - Change `DB_DATABASE=laverdad_herald`
   - Remove Render database credentials
3. Run `SETUP_DATABASE.bat`

### Keep Using Render Database (Not Recommended)
- Your current setup works but risks affecting production data
- Good for testing production issues locally

---

## 📝 File Priority

Laravel loads `.env` files in this order:
1. `.env` (main file - always used)
2. `.env.production` (if APP_ENV=production)
3. `.env.local` (if exists, overrides above)

**Bottom line:** `.env` is your main file. Others are backups/templates.

---

## 🔒 Security Notes

- ✅ `.env` files are in `.gitignore` (not pushed to GitHub)
- ✅ `.env.example` is safe to share (no real credentials)
- ⚠️ Never commit real `.env` files with passwords
- ⚠️ Your current `.env` has production database credentials

---

## 💡 Simple Rule

**Local work?** → Use `backend/.env` + `frontend/.env`
**Deploying?** → Platform uses `.env.production` files
**Sharing with team?** → Share `.env.example` only
