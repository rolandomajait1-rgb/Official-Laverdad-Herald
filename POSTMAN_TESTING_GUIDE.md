# Postman Testing Guide

## Step 1: Import Collection to Postman

### Option A: Import File
1. Open Postman
2. Click **"Import"** button (top left corner)
3. Click **"Upload Files"**
4. Select: `C:\Project\La_Verdad_Herald_API.postman_collection.json`
5. Click **"Import"**

### Option B: Drag & Drop
1. Open Postman
2. Drag `La_Verdad_Herald_API.postman_collection.json` into Postman window
3. Collection appears in left sidebar

## Step 2: Start Backend Server
```bash
cd C:\Project\backend
php artisan serve
```
Server runs at: http://localhost:8000

## Step 3: Test Endpoints

### Test 1: Register User
- Folder: **Auth** → **Register**
- Click **Send**
- Should return: User created + token

### Test 2: Login
- Folder: **Auth** → **Login**
- Click **Send**
- Token auto-saves to collection variable

### Test 3: Get Public Articles
- Folder: **Articles (Public)** → **Get Public Articles**
- Click **Send**
- No auth needed

### Test 4: Create Article (Admin Only)
- Folder: **Articles (Protected)** → **Create Article**
- Requires: Admin token
- Click **Send**

## Troubleshooting

**Can't find Import button?**
- Look top-left corner of Postman
- Orange/Blue button labeled "Import"

**Collection not showing?**
- Check left sidebar under "Collections"
- Click refresh icon

**401 Unauthorized?**
- Run Login request first
- Token auto-saves for other requests
