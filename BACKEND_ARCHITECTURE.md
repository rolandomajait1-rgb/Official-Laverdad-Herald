# La Verdad Herald - Backend Architecture & Breakdown

## 📋 Table of Contents

1. [Overview](#overview)
2. [Technology Stack](#technology-stack)
3. [Directory Structure](#directory-structure)
4. [Database Schema](#database-schema)
5. [API Routes & Endpoints](#api-routes--endpoints)
6. [Controllers & Business Logic](#controllers--business-logic)
7. [Models & Relationships](#models--relationships)
8. [Authentication & Security](#authentication--security)
9. [Configuration](#configuration)
10. [Deployment](#deployment)

---

## 🎯 Overview

**La Verdad Herald** is a Laravel 12 REST API backend serving a React Vite frontend. It manages a news/article publishing platform with user authentication, article management, categories, tags, and team features.

**Framework:** Laravel 12  
**Language:** PHP 8.2+  
**Database:** PostgreSQL (Render managed)  
**Auth:** Laravel Sanctum (token + cookie-based)  
**API Documentation:** Swagger/OpenAPI (L5-Swagger)  
**Deployment:** Render (free tier), Docker-ready

---

## 🛠️ Technology Stack

### Dependencies

```
PHP: ^8.2
Laravel: ^12.0
Laravel Sanctum: ^5.0 (API token authentication)
Laravel UI: ^4.5 (scaffolding)
Swagger: darkaonline/l5-swagger ^9.0 (OpenAPI docs)
```

### Dev Dependencies

```
PHPUnit: ^11.5.3 (testing)
Laravel Pint: ^1.24 (code formatting)
Faker: ^1.23 (test data generation)
Mockery: ^1.6 (mocking)
Collision: ^8.6 (error handling)
```

### Runtime

```
Server: PHP Built-in Server (dev) / Render (prod)
Web Server: Apache/Nginx compatible
SSL/TLS: Required for production CORS
```

---

## 📁 Directory Structure

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/          # 18 controllers for API endpoints
│   │   │   ├── ArticleController.php       # Article CRUD + publishing
│   │   │   ├── AuthController.php          # Login/Register
│   │   │   ├── CategoryController.php      # Category management
│   │   │   ├── TagController.php           # Tag management
│   │   │   ├── AuthorController.php        # Author profiles
│   │   │   ├── UserController.php          # User management
│   │   │   ├── ContactController.php       # Contact forms
│   │   │   ├── TeamMemberController.php    # Team profiles
│   │   │   ├── StaffController.php         # Staff management
│   │   │   ├── DraftController.php         # Draft articles
│   │   │   ├── SubscriberController.php    # Newsletter subscribers
│   │   │   ├── SearchController.php        # Full-text search
│   │   │   └── [Other Controllers]
│   │   └── Requests/             # Form validation (future)
│   │
│   ├── Models/                   # 11 Eloquent models
│   │   ├── User.php              # Users (with roles: user/admin/moderator)
│   │   ├── Article.php           # Published articles
│   │   ├── Draft.php             # Draft articles
│   │   ├── Author.php            # Author profiles
│   │   ├── Category.php          # Article categories
│   │   ├── Tag.php               # Article tags
│   │   ├── Subscriber.php        # Newsletter subscribers
│   │   ├── Staff.php             # Staff members
│   │   ├── TeamMember.php        # Team members
│   │   ├── ArticleInteraction.php # Likes/interactions
│   │   └── Log.php               # Activity logs
│   │
│   ├── Policies/                 # Authorization policies
│   └── Providers/                # Service providers
│
├── bootstrap/
│   └── app.php                   # Bootstrap configuration
│
├── config/                       # Configuration files
│   ├── app.php                   # App configuration
│   ├── auth.php                  # Authentication setup
│   ├── cors.php                  # CORS origins (Vercel domains)
│   ├── database.php              # Database connection
│   ├── session.php               # Session driver: 'file'
│   ├── cache.php                 # Cache store: 'file'
│   ├── sanctum.php               # Sanctum config
│   ├── mail.php                  # Mailer config (Brevo SMTP)
│   └── [Other configs]
│
├── database/
│   ├── migrations/               # 25 migrations
│   │   ├── create_users_table.php
│   │   ├── create_categories_table.php
│   │   ├── create_tags_table.php
│   │   ├── create_authors_table.php
│   │   ├── create_articles_table.php
│   │   ├── create_drafts_table.php
│   │   ├── create_subscribers_table.php
│   │   ├── create_team_members_table.php
│   │   ├── add_performance_indexes.php   # Index optimization
│   │   └── [Other migrations]
│   │
│   ├── factories/                # Model factories for testing
│   └── seeders/                  # Data seeders
│
├── routes/
│   ├── api.php                   # API routes (503 lines, 80+ endpoints)
│   ├── web.php                   # Web routes (mostly redirects)
│   └── console.php               # Artisan commands
│
├── storage/
│   ├── app/                      # File storage
│   ├── logs/                     # Application logs
│   └── framework/                # Cache/session files
│
├── tests/
│   ├── Unit/                     # Unit tests
│   └── Feature/                  # Feature tests
│
├── public/
│   ├── index.php                 # Entry point
│   ├── storage/                  # Public storage
│   └── images/                   # Public assets
│
├── Dockerfile                    # Docker containerization
├── docker-compose.yml            # Docker Compose config
├── Procfile                      # Render deployment config
├── render.yaml                   # Render service definition
├── nixpacks.toml                 # Nix package config
├── composer.json                 # PHP dependencies
├── package.json                  # Node.js scripts
├── phpunit.xml                   # Test configuration
└── artisan                       # Artisan CLI tool
```

---

## 📊 Database Schema

### Tables (25 migrations creating full schema)

```
USERS
├── id (PK)
├── name, email, password
├── email_verified_at
├── role (user/admin/moderator)
├── avatar (nullable)
└── timestamps

ARTICLES
├── id (PK)
├── title, slug, excerpt, content
├── featured_image (nullable)
├── status (draft/published)
├── author_id (FK → authors)
├── published_at (nullable)
├── timestamps
└── INDEXES: status, published_at, author_id

ARTICLE_CATEGORY (Pivot)
├── article_id (FK → articles)
└── category_id (FK → categories)

ARTICLE_TAG (Pivot)
├── article_id (FK → articles)
└── tag_id (FK → tags)

ARTICLE_USER_INTERACTIONS
├── id (PK)
├── article_id (FK → articles)
├── user_id (FK → users)
├── type (liked/viewed/commented)
└── timestamps

CATEGORIES
├── id (PK)
├── name, slug
├── description (nullable)
└── timestamps

TAGS
├── id (PK)
├── name, slug
└── timestamps

AUTHORS
├── id (PK)
├── user_id (FK → users)
├── bio (nullable)
└── timestamps

DRAFTS
├── id (PK)
├── title, slug, content, excerpt
├── author_id (FK → authors)
└── timestamps

SUBSCRIBERS
├── id (PK)
├── email (unique)
└── timestamps

STAFF
├── id (PK)
├── name, email
├── bio (nullable)
├── position (nullable)
├── avatar (nullable)
└── timestamps

TEAM_MEMBERS
├── id (PK)
├── name, email, role
├── bio (nullable)
└── timestamps

LOGS
├── id (PK)
├── action, description
├── user_id (FK → users, nullable)
└── timestamps

SESSIONS (Framework)
├── id (PK)
├── user_id (FK)
├── ip_address, user_agent
└── last_activity

CACHE (Framework)
├── key (PK)
├── value
└── expiration

JOBS (Queue)
├── id (PK)
├── queue, payload
├── exceptions, failed_at
└── timestamps

PERSONAL_ACCESS_TOKENS (Sanctum)
├── id (PK)
├── tokenable_type, tokenable_id
├── name, token
├── abilities (JSON)
└── last_used_at, expires_at
```

---

## 🛣️ API Routes & Endpoints

### Authentication (Public)

```
POST   /api/login                     Login user (rate: 5/min)
POST   /api/register                  Register new user (rate: 5/min)
GET    /api/email/verify/{id}/{hash}  Verify email address
```

### Contact & Feedback (Public, Rate Limited)

```
POST   /api/contact/feedback          Send feedback (10/min)
POST   /api/contact/request-coverage  Request article coverage (5/min)
POST   /api/contact/join-herald       Join as contributor (5/min)
POST   /api/contact/subscribe         Subscribe to newsletter (10/min)
```

### Articles (Public Read, Auth Write)

```
GET    /api/articles/public           Fetch all published articles (paginated)
GET    /api/articles/public/{id}      Get single article details
GET    /api/articles/search          Full-text search articles (min 3 chars)
POST   /api/articles                  Create new article (auth required)
PUT    /api/articles/{id}             Update article (auth required)
DELETE /api/articles/{id}             Delete article (auth required)
```

### Categories (Public Read)

```
GET    /api/categories               List all categories
GET    /api/categories/{id}          Get category with articles
```

### Tags (Public Read)

```
GET    /api/tags                     List all tags
```

### Authors (Public Read)

```
GET    /api/authors                  List all authors
GET    /api/authors/{id}             Get author profile with articles
```

### Drafts (Auth Required)

```
GET    /api/drafts                   List user's draft articles
POST   /api/drafts                   Create draft
PUT    /api/drafts/{id}              Update draft
DELETE /api/drafts/{id}              Delete draft
POST   /api/drafts/{id}/publish      Publish draft as article
```

### Team Members (Public Read, Auth Admin Write)

```
GET    /api/team-members             List all team members
POST   /api/team-members/update      Update profile (auth required)
```

### User Management (Auth Required)

```
GET    /api/me                       Get current user profile
PUT    /api/me                       Update profile
GET    /api/users                    List users (admin only)
```

### Subscribers (Internal)

```
POST   /api/subscribers              Add subscriber
```

### Logs (Admin Only)

```
GET    /api/logs                     View activity logs
```

**Total Endpoints:** 80+  
**Public Endpoints:** ~20 (no auth required)  
**Auth Endpoints:** ~60 (requires Sanctum token)  
**Rate Limiting:** Throttle middleware on contact/auth endpoints

---

## 🎮 Controllers & Business Logic

### AuthController

**Responsibility:** User authentication flows  
**Methods:**

- `loginApi()` - Login with email/password, returns JWT token
- `registerApi()` - Register new user, creates User + Author profile
- `logout()` - Revoke tokens (future)

**Features:**

- Rate limiting (5 attempts/minute)
- Email verification link generation
- Sanctum token generation
- Password hashing with bcrypt

### ArticleController

**Responsibility:** Article management  
**Methods:**

- `publicIndex()` - List published articles (pagination, eager load relations)
- `show()` - Get single article with interactions
- `store()` - Create article (auth required)
- `update()` - Update article
- `destroy()` - Delete article
- `publish()` - Publish draft

**Features:**

- Relationship eager loading (author, categories, tags)
- Article interaction tracking (likes, views)
- Status filtering (published/draft)
- Pagination (10 per page)

### CategoryController

**Responsibility:** Category management  
**Methods:**

- `index()` - List all categories
- `show()` - Get category with associated articles

**Features:**

- Slug-based URLs
- Relationship querying

### UserController

**Responsibility:** User account management  
**Methods:**

- `me()` - Get current authenticated user
- `update()` - Update user profile
- `index()` - List users (admin only)

**Features:**

- Authentication guard verification
- Role-based access control

### ContactController

**Responsibility:** Contact forms and feedback  
**Methods:**

- `sendFeedback()` - Process feedback form
- `requestCoverage()` - Request article coverage
- `joinHerald()` - Join as contributor
- `subscribe()` - Newsletter subscription

**Features:**

- CORS handling
- Rate limiting
- Email queuing (future: send via Brevo)

### TeamMemberController

**Responsibility:** Team profile management  
**Methods:**

- `index()` - List team members
- `update()` - Update team profile

### Other Controllers

- **DraftController** - Draft article management
- **AuthorController** - Author profiles
- **StaffController** - Staff directory
- **SubscriberController** - Newsletter subscribers
- **SearchController** - Full-text search
- **LogController** - Activity logs

---

## 🔗 Models & Relationships

### User Model

```php
class User implements MustVerifyEmail {
    Roles: user, admin, moderator
    Relations:
    ├── hasMany('Author')
    ├── hasMany('Interaction')
    └── hasMany('Token') // Sanctum
}
```

### Article Model

```php
class Article {
    Scopes:
    ├── published() - where status = 'published'
    ├── draft() - where status = 'draft'
    └── active() - ordered by published_at

    Relations:
    ├── belongsTo('Author')
    ├── belongsToMany('Category')
    ├── belongsToMany('Tag')
    └── hasMany('Interaction')

    Appends:
    ├── featured_image_url
    ├── is_liked
    └── likes_count
}
```

### Author Model

```php
class Author {
    Relations:
    ├── belongsTo('User')
    └── hasMany('Article')
}
```

### Category Model

```php
class Category {
    Relations:
    └── belongsToMany('Article')
}
```

### Tag Model

```php
class Tag {
    Relations:
    └── belongsToMany('Article')
}
```

### Subscriber Model

```php
class Subscriber {
    Attributes: email (unique)
}
```

### ArticleInteraction Model

```php
class ArticleInteraction {
    Types: 'liked', 'viewed', 'commented'

    Relations:
    ├── belongsTo('Article')
    └── belongsTo('User')
}
```

---

## 🔐 Authentication & Security

### Sanctum Setup

**How It Works:**

1. Frontend sends POST `/api/login` with email/password
2. Backend verifies credentials, creates Sanctum token
3. Frontend stores token in localStorage
4. Frontend sends token in `Authorization: Bearer <token>` header
5. Backend validates token via `auth:sanctum` middleware

**Routes Protected:**

- `/api/articles` (POST, PUT, DELETE)
- `/api/drafts` (all operations)
- `/api/me` (GET, PUT)
- `/api/team-members/update` (POST)
- `/api/users` (admin only)

**Config File:** `config/sanctum.php`

```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'localhost,127.0.0.1')),
'expiration' => null, // Tokens don't expire
'middleware' => ['verify_csrf_token', 'throttle:api'],
```

### CORS Configuration

**File:** `backend/config/cors.php`

**Origins Allowed:**

```php
'allowed_origins' => [
    'http://localhost:5173',                    // Dev frontend
    'https://official-laverdad-herald.vercel.app',     // Prod frontend
    'https://official-laverdad-herald-j84kxacox-*.vercel.app',  // Preview
    env('FRONTEND_URL', 'http://localhost:5173')
],

'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
'supports_credentials' => true,   // Allows cookies
```

### Rate Limiting

**Throttle Middleware:**

```php
Route::middleware('throttle:5,1')->group(function () {
    // 5 attempts per 1 minute
    Route::post('/login', ...);
    Route::post('/register', ...);
});

Route::middleware('throttle:10,1')->group(function () {
    // 10 attempts per 1 minute
    Route::post('/contact/feedback', ...);
});
```

### Session Management

**Config:** `config/session.php`

```php
'driver' => env('SESSION_DRIVER', 'file'),  // File-based (no DB dependency)
'lifetime' => 120,     // 2 hours
'secure_cookie' => env('SESSION_SECURE_COOKIE', true)  // HTTPS only
```

### Password Hashing

**Method:** Bcrypt (Laravel default)

```php
Hash::make($password)      // Hash password
Hash::check($password, $hash)  // Verify password
```

---

## ⚙️ Configuration

### Environment Files

**Development:** `.env`

```
APP_ENV=local
APP_DEBUG=true
DATABASE=pgsql with Render credentials
SESSION_DRIVER=file
CACHE_STORE=file
FRONTEND_URL=http://localhost:5173
```

**Production:** `.env.render` (reference)

```
APP_ENV=production
APP_DEBUG=false
DATABASE=pgsql with Render credentials
SESSION_DRIVER=file
CACHE_STORE=file
FRONTEND_URL=https://official-laverdad-herald.vercel.app
```

**Render Dashboard** (Source of Truth)

```
DATABASE_URL=postgresql://...
SESSION_DRIVER=file
CACHE_STORE=file
FRONTEND_URL=https://official-laverdad-herald.vercel.app
```

### Key Configuration Files

**`config/app.php`**

- Name: "La Verdad Herald"
- Timezone: UTC
- Locale: en_US

**`config/database.php`**

- Default: pgsql
- Host: dpg-d64rrekhg0os73df6t20-a.oregon-postgres.render.com
- SSL Mode: require (for Render)

**`config/cache.php`**

- Default: file
- Path: storage/framework/cache

**`config/logging.php`**

- Channel: stack
- Log Level: debug/production

**`config/mail.php`**

- Driver: smtp
- Host: smtp-relay.brevo.com
- Port: 587
- Auth: email/password

---

## 🚀 Deployment

### Render Deployment

**Procfile:**

```bash
web: php artisan migrate --force 2>/dev/null || true && \
     php artisan config:cache && \
     php artisan route:cache && \
     php artisan view:cache && \
     php -S 0.0.0.0:$PORT -t public
```

**Build Process:**

1. Install PHP dependencies: `composer install`
2. Run migrations: `php artisan migrate --force`
3. Cache config/routes for performance
4. Start PHP built-in server on dynamic PORT

**Free Tier Limits:**

- 2 cores / 8GB RAM
- Spins down after 15 min inactivity
- Cold start: ~30 sec wake-up time

### Docker Support

**Dockerfile:** Multi-stage build (future deployment option)

- Base: PHP 8.2
- Extensions: pgsql, curl, mbstring
- Composer: auto-install deps

### GitHub Deployment

**CI/CD Hook:**

- Push to `main` branch → Render rebuilds automatically
- Vercel also monitors for frontend rebuild

---

## 📈 Performance Optimizations

### Database Indexes

**Migration:** `add_performance_indexes.php`

```sql
INDEX articles(status, published_at)
INDEX articles(author_id)
INDEX article_interactions(article_id, user_id)
INDEX article_category(article_id, category_id)
INDEX article_tag(article_id, tag_id)
```

### Eager Loading

**Pattern:** Prevent N+1 queries

```php
Article::with(['author.user', 'categories', 'tags'])->get()
```

### Query Optimization

- Pagination (10 items/page)
- Soft deletes (if enabled)
- Query scopes for common filters

### Caching

- Config cache: `php artisan config:cache`
- Route cache: `php artisan route:cache`
- View cache: `php artisan view:cache`

---

## 🧪 Testing

### Test Structure

```
tests/
├── Unit/
│   └── Models/ (model logic)
└── Feature/
    └── Http/Controllers/ (API endpoints)
```

### Running Tests

```bash
php artisan test
php artisan test --filter=ArticleTest
php artisan test --coverage
```

### Test Database

- Separate test database (in-memory SQLite for speed)
- Database transactions rollback after each test
- Faker generates test data

---

## 📝 Development Workflow

### Local Setup

```bash
# 1. Clone repository
git clone <repo>
cd backend

# 2. Install dependencies
composer install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Database
php artisan migrate
php artisan db:seed

# 5. Start server
php artisan serve --port=8000

# 6. Access API
http://localhost:8000/api/articles/public
```

### Artisan Commands

```bash
# Database
php artisan migrate              # Run migrations
php artisan migrate:fresh        # Reset and reseed
php artisan db:seed              # Run seeders

# Cache
php artisan config:cache         # Cache config
php artisan route:cache          # Cache routes
php artisan view:cache           # Cache views

# Development
php artisan tinker               # Interactive shell
php artisan make:model Article   # Generate model
php artisan make:migration create_table  # Generate migration
```

---

## 🔍 API Documentation

### Swagger/OpenAPI

**Package:** darkaonline/l5-swagger

**Accessing Docs (when enabled):**

```
http://localhost:8000/api/documentation
```

**Annotation Example:**

```php
/**
 * @OA\Get(
 *     path="/api/articles/public",
 *     tags={"Articles"},
 *     summary="List public articles",
 *     @OA\Response(response=200, description="Success")
 * )
 */
```

---

## 🎯 Key Statistics

| Metric              | Count  |
| ------------------- | ------ |
| Controllers         | 18     |
| Models              | 11     |
| Migrations          | 25     |
| API Endpoints       | 80+    |
| Database Tables     | 15     |
| Public Endpoints    | 20     |
| Protected Endpoints | 60     |
| Dependencies        | 8 main |
| Dev Dependencies    | 9 dev  |

---

## 💡 Architecture Decisions

### Why File-Based Sessions/Cache?

- **Free tier** Render doesn't have Redis
- **Stateless**: Easy to scale horizontally
- **Simple**: No dependency on external services

### Why Sanctum Over Passport?

- **Lightweight**: Perfect for SPA + mobile APIs
- **Cookie + Token**: Supports both auth patterns
- **Built-in**: Official Laravel package
- **No DB dependency**: Unlike Passport

### Why PostgreSQL?

- **Robust**: Production-grade relational DB
- **Render Support**: Free tier includes 100 connections
- **ACID Compliance**: Data integrity
- **JSON Support**: Future extensibility

### Why Render for Backend?

- **Free tier**: Good for learning/small projects
- **Git integration**: Auto-deploy on push
- **Managed DB**: No server admin needed
- **Scaling**: Easy upgrade when needed

---

## 📚 Resources & References

**Laravel Documentation:**

- https://laravel.com/docs
- https://sanctum.laravel.com
- https://laravel.com/docs/testing

**API Best Practices:**

- RESTful conventions followed
- Pagination on list endpoints
- Appropriate HTTP status codes
- JSON responses

**GitHub Repository:**

- https://github.com/rolandomajait1-rgb/Official-Laverdad-Herald

---

## 🛠️ Next Steps for Development

1. **Add Form Validation** - Form Requests in Controllers
2. **Implement Policies** - Authorization for resource operations
3. **Add Event Listeners** - Email on article published, etc.
4. **API Versioning** - Support `/api/v1/` and `/api/v2/`
5. **Webhooks** - Notify external services on events
6. **Caching Layer** - Redis for performance
7. **API Rate Limiting** - Adjust per endpoint
8. **Monitoring** - Error tracking, performance metrics
9. **CI/CD Testing** - Automated tests before merge
10. **Database Backup** - Automated backups to S3

---

Created: February 10, 2026  
Last Updated: Latest commit `afcd92f`  
Environment: Local Dev + Render Production
