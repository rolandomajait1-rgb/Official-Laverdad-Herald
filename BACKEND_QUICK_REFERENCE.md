# Backend Quick Reference Guide

## 🚀 Quick Start (5 Minutes)

### Start Backend Locally
```bash
cd backend
php artisan serve --port=8000
```
✓ Backend running on http://localhost:8000

### Test API Endpoints
```bash
# Get public articles
curl http://localhost:8000/api/articles/public

# Get categories
curl http://localhost:8000/api/categories

# Register user
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "password": "Password123!",
    "password_confirmation": "Password123!"
  }'

# Login (get token)
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email": "john@example.com", "password": "Password123!"}'

# Use token to access protected endpoint
TOKEN="your_token_here"
curl http://localhost:8000/api/me \
  -H "Authorization: Bearer $TOKEN"
```

---

## 📊 Database Quick Reference

### Most Used Tables

| Table | Purpose | Key Fields |
|-------|---------|-----------|
| `users` | User accounts | id, email, password, role |
| `articles` | Published articles | id, title, content, author_id, status |
| `categories` | Article categories | id, name, slug |
| `tags` | Article tags | id, name, slug |
| `authors` | Author profiles | id, user_id, bio |
| `drafts` | Draft articles | id, title, author_id |
| `article_interactions` | Likes/views | id, article_id, user_id, type |

### Query Database Directly
```bash
# Access PostgreSQL
psql "postgresql://user:pass@host:5432/database"

# Common queries
SELECT COUNT(*) FROM articles WHERE status = 'published';
SELECT * FROM categories ORDER BY name;
SELECT * FROM users WHERE role = 'admin';
```

---

## 🎮 Controller Method Mapping

### Article Management
```
GET    /api/articles/public        → ArticleController@publicIndex()
GET    /api/articles/{id}          → ArticleController@show()
POST   /api/articles               → ArticleController@store() [Auth]
PUT    /api/articles/{id}          → ArticleController@update() [Auth]
DELETE /api/articles/{id}          → ArticleController@destroy() [Auth]
GET    /api/articles/search?q=     → ArticleController@search()
```

### Authentication
```
POST /api/login                    → AuthController@loginApi()
POST /api/register                 → AuthController@registerApi()
GET  /api/me                       → UserController@me() [Auth]
```

### Taxonomy
```
GET /api/categories                → CategoryController@index()
GET /api/tags                      → TagController@index()
GET /api/authors                   → AuthorController@index()
```

### Contact Forms
```
POST /api/contact/feedback         → ContactController@sendFeedback()
POST /api/contact/subscribe        → ContactController@subscribe()
POST /api/contact/join-herald      → ContactController@joinHerald()
POST /api/contact/request-coverage → ContactController@requestCoverage()
```

---

## 🔑 Authentication & Authorization

### Token-Based Auth (Sanctum)
```php
// Get token on login
$token = $user->createToken('api-token')->plainTextToken;

// Use token in requests
Authorization: Bearer {token}

// Check in controller
if (Auth::guard('sanctum')->check()) {
    $user = Auth::user();  // Get current user
}
```

### Route Protection
```php
// Public route
Route::get('/api/articles/public', [...]);

// Protected route
Route::middleware('auth:sanctum')->post('/api/articles', [...]);

// Admin only
Route::middleware(['auth:sanctum', 'admin'])->get('/api/users', [...]);
```

### User Roles
```
ROLE_USER      = 'user'
ROLE_ADMIN     = 'admin'
ROLE_MODERATOR = 'moderator'

// Check role in controller
if ($user->hasRole('admin')) { ... }
if ($user->isModerator()) { ... }
```

---

## 🔧 Common Artisan Commands

### Database
```bash
php artisan migrate              # Run all migrations
php artisan migrate:fresh        # Drop all tables and re-migrate
php artisan migrate:rollback     # Undo last migration batch
php artisan db:seed              # Run seeders (populate test data)
php artisan tinker               # Interactive shell
```

### Cache & Performance
```bash
php artisan config:cache         # Cache all config files
php artisan route:cache          # Cache all routes
php artisan view:cache           # Cache blade views
```

### Generation
```bash
php artisan make:controller UserController
php artisan make:model Article
php artisan make:migration create_users_table
php artisan make:seeder UserSeeder
```

### Debugging
```bash
php artisan logs                 # Tail application log
php artisan env                  # Show current environment
php artisan --version            # Show Laravel version
```

---

## 📝 Key File Locations

| File/Folder | Purpose |
|-------------|---------|
| `app/Models/` | Database models (Article, User, etc.) |
| `app/Http/Controllers/` | Request handlers |
| `routes/api.php` | API endpoint definitions |
| `database/migrations/` | Database schema changes |
| `config/cors.php` | CORS origins configuration |
| `config/session.php` | Session driver (file/database) |
| `storage/logs/laravel.log` | Application error log |
| `.env` | Environment variables (local) |
| `Procfile` | Render deployment config |

---

## 🌐 API Response Format

### Success Response (200 OK)
```json
{
  "data": [
    {
      "id": 1,
      "title": "Article Title",
      "slug": "article-title",
      "excerpt": "...",
      "content": "...",
      "author": { "id": 1, "name": "John" },
      "categories": [...],
      "tags": [...]
    }
  ],
  "current_page": 1,
  "total": 15,
  "per_page": 10
}
```

### Paginated Response
```json
{
  "data": [...],
  "current_page": 1,
  "first_page_url": "http://api.local/api/articles?page=1",
  "from": 1,
  "last_page": 2,
  "last_page_url": "http://api.local/api/articles?page=2",
  "next_page_url": "http://api.local/api/articles?page=2",
  "path": "http://api.local/api/articles",
  "per_page": 10,
  "prev_page_url": null,
  "to": 10,
  "total": 15
}
```

### Error Response (4xx/5xx)
```json
{
  "message": "Error description",
  "errors": {
    "email": ["Email field is required"]
  }
}
```

---

## 🔍 Debugging Tips

### View Application Log
```bash
tail -f storage/logs/laravel.log
# or
php artisan logs
```

### Database Query Logging
```php
// In controller
DB::enableQueryLog();
// ... do query
dd(DB::getQueryLog());
```

### Check Auth Status
```bash
php artisan tinker
>>> Auth::check()
>>> Auth::user()
>>> Auth::guard('sanctum')->check()
```

### Test Routes
```bash
php artisan route:list
php artisan route:list --path=api
```

---

## 📋 Configuration Checklist

### For Local Development
- [ ] `.env` file copied from `.env.example`
- [ ] Database credentials configured
- [ ] `php artisan migrate` ran successfully
- [ ] Backend server running on port 8000
- [ ] CORS allows localhost:5173
- [ ] SESSION_DRIVER=file (or database)

### For Production (Render)
- [ ] Environment variables set in Render Dashboard
- [ ] DATABASE_URL configured
- [ ] FRONTEND_URL set to Vercel domain
- [ ] SESSION_DRIVER=file (no Redis needed)
- [ ] CACHE_STORE=file (no Redis needed)
- [ ] Migrations run on startup (Procfile)

---

## 🚨 Common Issues & Solutions

### Issue: CORS Error
**Error:** "No 'Access-Control-Allow-Origin' header"
**Solution:**
1. Check Vercel domain is in `config/cors.php` `allowed_origins`
2. Verify `supports_credentials` is `true`
3. Restart backend after config change

### Issue: 401 Unauthorized
**Error:** "Unauthenticated" on protected routes
**Solution:**
1. Verify token is being sent in header: `Authorization: Bearer {token}`
2. Check token hasn't expired
3. Ensure `auth:sanctum` middleware is applied
4. Verify token exists in `personal_access_tokens` table

### Issue: 422 Unprocessable Entity
**Error:** Validation error on POST request
**Solution:**
1. Check request has `Content-Type: application/json`
2. Verify all required fields are present
3. Check error response for `errors` field
4. Validate field formats (email, password strength, etc.)

### Issue: 500 Internal Server Error
**Error:** "Something went wrong"
**Solution:**
1. Check `storage/logs/laravel.log` for error details
2. Verify database connection
3. Check if migrations ran successfully
4. Set `APP_DEBUG=true` for detailed error messages

### Issue: Database Connection Failed
**Error:** "could not connect to server"
**Solution:**
1. Verify PostgreSQL is running (local)
2. Check `.env` database credentials
3. Verify host/port are correct
4. For Render: check if database service is active

---

## 📊 Performance Optimization Tips

### Enable Caching
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Use Eager Loading
```php
// Bad - N+1 queries
$articles = Article::all();
foreach ($articles as $article) {
    echo $article->author->name;  // Query inside loop
}

// Good - Eager load
$articles = Article::with('author')->get();
foreach ($articles as $article) {
    echo $article->author->name;  // No additional queries
}
```

### Add Database Indexes
```php
// Migration
Schema::table('articles', function (Blueprint $table) {
    $table->index('status');
    $table->index('published_at');
});
```

### Use Pagination
```php
// Instead of
$articles = Article::all();  // Load ALL articles

// Use
$articles = Article::paginate(15);  // Load 15 per page
```

---

## 🧪 Testing API Locally

### Using Postman
1. Open Postman
2. Create new request
3. Set method (GET/POST/etc.)
4. Enter URL: http://localhost:8000/api/articles/public
5. Add headers if needed: `Content-Type: application/json`
6. Send request

### Using curl
```bash
# GET request
curl http://localhost:8000/api/categories

# POST request
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"pass"}'

# With authentication
curl http://localhost:8000/api/me \
  -H "Authorization: Bearer TOKEN_HERE"
```

### Using Postman Collection
1. Import `La_Verdad_Herald_API.postman_collection.json`
2. Set collection variables (base_url, token)
3. Run requests from collection

---

## 📞 Support & Resources

### Useful Links
- Laravel Docs: https://laravel.com/docs
- Sanctum Docs: https://laravel.com/docs/sanctum
- PostgreSQL: https://www.postgresql.org/docs
- Render Docs: https://render.com/docs

### JSON Reference
- `id` - Unique identifier (integer)
- `slug` - URL-friendly name (lowercase, hyphens)
- `created_at` - Timestamp of creation
- `updated_at` - Timestamp of last update
- `published_at` - Timestamp of publication (nullable)
- `status` - State: draft/published/archived

### HTTP Status Codes
- `200 OK` - Request successful
- `201 Created` - Resource created successfully
- `204 No Content` - Success with no response body
- `400 Bad Request` - Invalid request format
- `401 Unauthorized` - Missing or invalid token
- `403 Forbidden` - Insufficient permissions
- `404 Not Found` - Resource doesn't exist
- `422 Unprocessable Entity` - Validation error
- `429 Too Many Requests` - Rate limit exceeded
- `500 Internal Server Error` - Server error

---

## ✅ Pre-Deployment Checklist

- [ ] All tests passing: `php artisan test`
- [ ] Database migrations completed
- [ ] No console errors: `tail storage/logs/laravel.log`
- [ ] CORS configured for production domain
- [ ] Session driver set to 'file' (Render free tier)
- [ ] Cache store set to 'file' (no Redis)
- [ ] All secrets in environment variables (not in code)
- [ ] Rate limiting configured per endpoint
- [ ] API documentation up to date
- [ ] Backup of current database taken

---

Last Updated: February 10, 2026  
Framework: Laravel 12  
Status: Production Ready  
Support: GitHub Issues
