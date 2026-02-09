# MySQL XAMPP Startup Error Fix

## Quick Fixes (Try in Order)

### Fix 1: Port Conflict (Most Common)
Another service is using port 3306.

**Solution:**
1. Open XAMPP Control Panel as Administrator
2. Click "Config" button next to MySQL
3. Select "my.ini"
4. Find line: `port=3306`
5. Change to: `port=3307`
6. Save file
7. Update `backend/.env`:
   ```
   DB_PORT=3307
   ```
8. Start MySQL

### Fix 2: Corrupted MySQL Data
**Solution:**
1. Stop MySQL in XAMPP
2. Go to: `C:\xampp\mysql\data`
3. Backup `ibdata1` file (copy to safe location)
4. Delete these files:
   - `ibdata1`
   - `ib_logfile0`
   - `ib_logfile1`
5. Copy backup files from: `C:\xampp\mysql\backup`
6. Start MySQL

### Fix 3: Use SQLite Instead (Fastest)
Skip MySQL entirely and use SQLite for local development.

**Solution:**
1. Update `backend/.env`:
   ```
   DB_CONNECTION=sqlite
   # Comment out MySQL settings
   # DB_HOST=127.0.0.1
   # DB_PORT=3306
   # DB_DATABASE="final laverdad herald"
   # DB_USERNAME=root
   # DB_PASSWORD=
   ```

2. Create SQLite database:
   ```bash
   cd backend
   touch database/database.sqlite
   ```

3. Run migrations:
   ```bash
   php artisan migrate
   ```

4. Start server:
   ```bash
   php artisan serve
   ```

### Fix 4: Reinstall MySQL Service
**Solution:**
1. Open Command Prompt as Administrator
2. Navigate to XAMPP:
   ```
   cd C:\xampp\mysql\bin
   ```
3. Remove service:
   ```
   mysqld --remove
   ```
4. Reinstall service:
   ```
   mysqld --install
   ```
5. Start MySQL in XAMPP

## Recommended: Use SQLite for Development

**Advantages:**
- No port conflicts
- No service issues
- Faster setup
- Same Laravel features
- Easy to reset (just delete database.sqlite file)

**For Production:**
- Railway will use MySQL automatically
- Your production `.env` already configured for MySQL
- No code changes needed

## After Fix

Once MySQL/SQLite is running:

```bash
cd backend
php artisan migrate
php artisan db:seed
php artisan serve
```

Then test at: http://localhost:8000
