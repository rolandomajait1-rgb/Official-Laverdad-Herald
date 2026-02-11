# Senior Developer Endpoint Audit Report
**La Verdad Herald - Frontend & Backend Analysis**

---

## 🔴 CRITICAL ISSUES

### 1. **Hardcoded URLs in Frontend**
**Severity: HIGH**

**Location:** `frontend/src/pages/HomePage.jsx` (Line 48)
```javascript
fetch(`http://localhost:8000/api/categories/${category}/articles`)
```

**Location:** `frontend/src/AdminDashboard/CreateArticle.jsx` (Lines 107, 165)
```javascript
fetch('http://localhost:8000/api/articles', {...})
```

**Problem:**
- Hardcoded localhost URLs will break in production
- Inconsistent with the axios configuration that uses `VITE_API_BASE_URL`
- Some components use axios (configured properly), others use raw fetch

**Impact:** Application will fail in production deployment

**Fix Required:**
```javascript
// Use axios or construct URL from env
import axios from '../utils/axiosConfig';
const response = await axios.get(`/api/categories/${category}/articles`);
```

---

### 2. **CORS Headers Hardcoded to Production Domain**
**Severity: HIGH**

**Location:** `backend/routes/api.php` (Multiple locations)
```php
->header('Access-Control-Allow-Origin', 'https://laverdad.edu.ph')
```

**Problem:**
- Hardcoded production domain in CORS headers
- Will block local development requests
- Inconsistent CORS handling (some use CorsHelper, others inline headers)

**Impact:** Local development will fail with CORS errors

**Fix Required:**
```php
// Use environment-based CORS configuration
// In config/cors.php - already configured properly
// Remove inline CORS headers and rely on Laravel's CORS middleware
```

---

### 3. **Inconsistent API Base URL Configuration**
**Severity: MEDIUM**

**Frontend has TWO different API configurations:**

**File 1:** `frontend/src/utils/api.js`
```javascript
const rawBase = import.meta.env.VITE_API_BASE_URL || "http://localhost:8000/api";
const BASE_ROOT = String(rawBase).replace(/\/api\/?$/i, "");
const API_BASE_URL = `${BASE_ROOT}/api`;
```

**File 2:** `frontend/src/utils/axiosConfig.js`
```javascript
const rawBase = import.meta.env.VITE_API_BASE_URL || "http://localhost:8000";
const BASE_ROOT = String(rawBase).replace(/\/api\/?$/i, "");
axios.defaults.baseURL = BASE_ROOT;
```

**Problem:**
- Two different API configuration files doing similar things
- `api.js` is barely used (only exports apiEndpoints and apiRequest)
- Most components use axios directly or raw fetch
- Confusing for developers

**Recommendation:** Consolidate to single source of truth (axiosConfig.js)

---

## ⚠️ MAJOR ISSUES

### 4. **Missing Authentication on Public Endpoints**
**Severity: MEDIUM**

**Location:** `backend/routes/api.php`

Several endpoints that should potentially be protected:
```php
Route::get('/test-drafts', function () {...}); // Line 213
Route::put('/test-publish/{id}', function () {...}); // Line 217
Route::get('/storage-link', function () {...}); // Line 223
```

**Problem:**
- Test/debug endpoints exposed in production
- No authentication required
- Could be exploited

**Fix Required:** Remove or protect with auth middleware

---

### 5. **Unreachable Code in api.php**
**Severity: LOW**

**Location:** `backend/routes/api.php` (Line 207-218)
```php
Route::get('/latest-articles', function () {
    $articles = Article::published()
        ->with('author.user', 'categories')
        ->latest('published_at')
        ->take(6)
        ->get();
        
    return CorsHelper::addHeaders(response()->json($articles));
    
    // UNREACHABLE CODE BELOW
    $allArticles = Article::with('author.user', 'categories')->get();
    $publishedArticles = Article::published()->with('author.user', 'categories')->get();
    // ... more unreachable code
});
```

**Problem:** Dead code after return statement

**Fix Required:** Remove unreachable code

---

### 6. **Inconsistent Error Handling**
**Severity: MEDIUM**

**Frontend:** Different error handling patterns across components

**Example 1:** `HomePage.jsx`
```javascript
catch (err) {
    console.error('Error fetching home page articles:', err);
    setError(`Failed to load articles: ${err.response?.status || err.message}`);
}
```

**Example 2:** `ArticleDetail.jsx`
```javascript
catch (err) {
    console.error('Error fetching article:', err);
    setError('Article not found');
}
```

**Problem:**
- Inconsistent error messages
- Some show technical details, others generic messages
- No centralized error handling

**Recommendation:** Create error handling utility

---

### 7. **Missing Input Validation on Frontend**
**Severity: MEDIUM**

**Location:** `CreateArticle.jsx`

Form validation only checks if fields are filled:
```javascript
const valid = title.trim() && category && content.trim() && tags.length > 0 && authorName.trim();
```

**Missing:**
- Title length validation (backend has max:255)
- Content length validation
- Image size validation (backend has max:5120KB)
- Tag format validation

**Impact:** Users get errors after submission instead of real-time feedback

---

## 📋 ARCHITECTURAL CONCERNS

### 8. **Mixed API Call Patterns**
**Severity: MEDIUM**

The frontend uses THREE different ways to make API calls:

1. **Axios with config** (Recommended)
```javascript
import axios from '../utils/axiosConfig';
await axios.get('/api/articles');
```

2. **Raw fetch with hardcoded URL** (Bad)
```javascript
fetch('http://localhost:8000/api/articles')
```

3. **apiRequest utility** (Unused)
```javascript
import { apiRequest } from '../utils/api';
await apiRequest(url, options);
```

**Recommendation:** Standardize on axios with axiosConfig.js

---

### 9. **Duplicate CORS Handling**
**Severity: LOW**

**Backend has multiple CORS implementations:**

1. **CorsHelper class** (`app/Http/Helpers/CorsHelper.php`)
2. **Inline CORS headers** in routes
3. **Laravel CORS middleware** (config/cors.php)
4. **Manual OPTIONS routes**

**Problem:**
- Redundant code
- Maintenance nightmare
- Potential conflicts

**Recommendation:** Use only Laravel's built-in CORS middleware

---

### 10. **Inconsistent Response Formats**
**Severity: LOW**

**Backend returns different response structures:**

**Format 1:** Direct data
```php
return response()->json($articles);
```

**Format 2:** Wrapped in 'data'
```php
return response()->json(['data' => $articles]);
```

**Format 3:** Custom structure
```php
return response()->json([
    'articles' => $articles,
    'article_count' => $count,
    'author' => $author
]);
```

**Impact:** Frontend needs different parsing logic for different endpoints

---

## 🔒 SECURITY ISSUES

### 11. **Weak Rate Limiting**
**Severity: MEDIUM**

**Location:** `backend/routes/api.php`
```php
Route::middleware('throttle:5,1')->group(function () {
    Route::post('/login', [AuthController::class, 'loginApi']);
    Route::post('/register', [AuthController::class, 'registerApi']);
});
```

**Analysis:**
- Login: 5 attempts per minute (Good)
- Contact forms: 5-10 per minute (May be too restrictive for legitimate users)
- No rate limiting on article creation/updates

**Recommendation:** Review and adjust based on expected usage

---

### 12. **XSS Vulnerability Risk**
**Severity: MEDIUM**

**Location:** `frontend/src/pages/ArticleDetail.jsx`
```javascript
<div dangerouslySetInnerHTML={{ __html: DOMPurify.sanitize(article.content) }} />
```

**Good:** Using DOMPurify
**Concern:** Content is stored as HTML in database, sanitization happens client-side

**Recommendation:** Sanitize on backend before storing

---

### 13. **Missing CSRF Protection on API Routes**
**Severity: LOW**

**Location:** `backend/routes/api.php`

API routes use Sanctum tokens but don't require CSRF tokens for state-changing operations.

**Current:** Sanctum provides token-based auth
**Note:** This is acceptable for SPA, but ensure tokens are properly secured

---

## 🎯 ENDPOINT MAPPING ANALYSIS

### Backend Endpoints (api.php)

#### Public Endpoints (No Auth)
| Method | Endpoint | Controller/Closure | Issues |
|--------|----------|-------------------|--------|
| GET | `/team-members` | TeamMemberController@index | ✅ OK |
| POST | `/login` | AuthController@loginApi | ⚠️ Hardcoded CORS |
| POST | `/register` | AuthController@registerApi | ⚠️ Hardcoded CORS |
| GET | `/email/verify/{id}/{hash}` | Closure | ✅ OK |
| POST | `/email/resend-verification` | AuthController@resendVerificationEmail | ✅ OK |
| POST | `/contact/feedback` | ContactController@sendFeedback | ⚠️ Manual OPTIONS route |
| POST | `/contact/request-coverage` | ContactController@requestCoverage | ⚠️ Manual OPTIONS route |
| POST | `/contact/join-herald` | ContactController@joinHerald | ⚠️ Manual OPTIONS route |
| POST | `/contact/subscribe` | ContactController@subscribe | ⚠️ Manual OPTIONS route |
| GET | `/categories` | CategoryController@index | ✅ OK |
| GET | `/articles/public` | ArticleController@publicIndex | ⚠️ Manual OPTIONS route |
| GET | `/articles/search` | Closure | ✅ OK |
| GET | `/articles/by-slug/{slug}` | Closure | ✅ OK |
| GET | `/articles/author-public/{authorId}` | ArticleController@getArticlesByAuthorPublic | ✅ OK |
| GET | `/authors` | Closure | ✅ OK |
| GET | `/articles/id/{id}` | Closure | ✅ OK |
| GET | `/authors/{authorName}` | Closure | ✅ OK |
| GET | `/latest-articles` | Closure | 🔴 Unreachable code |
| GET | `/test-drafts` | Closure | 🔴 Debug endpoint exposed |
| PUT | `/test-publish/{id}` | Closure | 🔴 Debug endpoint exposed |
| GET | `/storage-link` | Closure | 🔴 Utility endpoint exposed |
| GET | `/categories/{category}/articles` | Closure | ⚠️ Manual CORS headers |
| GET | `/tags` | TagController@index | ✅ OK |
| GET | `/tags/{tag}` | TagController@show | ✅ OK |
| GET | `/logs` | LogController@index | ⚠️ Should be protected |
| GET | `/logs/{log}` | LogController@show | ⚠️ Should be protected |

#### Protected Endpoints (auth:sanctum)
| Method | Endpoint | Middleware | Controller | Issues |
|--------|----------|-----------|------------|--------|
| POST | `/team-members/update` | auth:sanctum | TeamMemberController@update | ✅ OK |
| GET | `/user` | auth:sanctum | Closure | ⚠️ Hardcoded CORS |
| POST | `/logout` | auth:sanctum | AuthController@logoutApi | ✅ OK |
| POST | `/change-password` | auth:sanctum | AuthController@changePasswordApi | ✅ OK |
| POST | `/delete-account` | auth:sanctum | AuthController@deleteAccountApi | ✅ OK |
| GET | `/user/liked-articles` | auth:sanctum | ArticleController@getLikedArticles | ✅ OK |
| GET | `/user/shared-articles` | auth:sanctum | ArticleController@getSharedArticles | ✅ OK |
| GET | `/articles` | auth:sanctum | ArticleController@index | ✅ OK |
| POST | `/articles` | auth:sanctum | ArticleController@store | ✅ OK |
| GET | `/articles/{article}` | auth:sanctum | ArticleController@show | ✅ OK |
| PUT | `/articles/{article}` | auth:sanctum | ArticleController@update | ✅ OK |
| DELETE | `/articles/{article}` | auth:sanctum | ArticleController@destroy | ✅ OK |
| POST | `/articles/{article}/like` | auth:sanctum | ArticleController@like | ✅ OK |
| GET | `/articles/author/{authorId}` | auth:sanctum | ArticleController@getArticlesByAuthor | ✅ OK |
| POST | `/categories` | auth:sanctum | CategoryController@store | ✅ OK |
| GET | `/categories/{category}` | auth:sanctum | CategoryController@show | ✅ OK |
| PUT | `/categories/{category}` | auth:sanctum | CategoryController@update | ✅ OK |
| DELETE | `/categories/{category}` | auth:sanctum | CategoryController@destroy | ✅ OK |
| POST | `/tags` | auth:sanctum | TagController@store | ✅ OK |
| PUT | `/tags/{tag}` | auth:sanctum | TagController@update | ✅ OK |
| DELETE | `/tags/{tag}` | auth:sanctum | TagController@destroy | ✅ OK |
| * | `/subscribers` | auth:sanctum | SubscriberController (Resource) | ✅ OK |

#### Admin/Moderator Endpoints
| Method | Endpoint | Middleware | Issues |
|--------|----------|-----------|--------|
| GET | `/admin/dashboard-stats` | auth:sanctum, role:admin,moderator | ⚠️ Hardcoded CORS |
| GET | `/admin/recent-activity` | auth:sanctum, role:admin,moderator | ⚠️ Hardcoded CORS |
| GET | `/admin/audit-logs` | auth:sanctum, role:admin,moderator | ⚠️ Hardcoded CORS |

#### Admin-Only Endpoints
| Method | Endpoint | Middleware | Issues |
|--------|----------|-----------|--------|
| GET | `/admin/check-access` | auth:sanctum, role:admin | ⚠️ Hardcoded CORS |
| POST | `/admin/reset-data` | auth:sanctum, role:admin | 🔴 Dangerous endpoint |
| GET | `/admin/stats` | auth:sanctum, role:admin | ⚠️ Hardcoded CORS |
| GET | `/admin/moderators` | auth:sanctum, role:admin | ✅ OK |
| POST | `/admin/moderators` | auth:sanctum, role:admin | ✅ OK |
| DELETE | `/admin/moderators/{id}` | auth:sanctum, role:admin | ✅ OK |
| * | `/admin/users` | auth:sanctum, role:admin | ✅ OK |
| * | `/staff` | auth:sanctum, role:admin | ✅ OK |

#### Moderator Endpoints
| Method | Endpoint | Middleware | Issues |
|--------|----------|-----------|--------|
| * | `/drafts` | auth:sanctum, role:moderator | ✅ OK (except store) |

#### Author Endpoints
| Method | Endpoint | Middleware | Issues |
|--------|----------|-----------|--------|
| * | `/drafts` | auth:sanctum, role:author | ✅ OK (limited methods) |

---

### Frontend API Usage Analysis

#### Files Using Hardcoded URLs (CRITICAL)
1. `HomePage.jsx` - Line 48
2. `CreateArticle.jsx` - Lines 107, 165

#### Files Using Axios Correctly
1. `ArticleDetail.jsx` ✅
2. `axiosConfig.js` ✅

#### Files Using apiRequest Utility
- None found (utility is unused)

---

## 🔧 RECOMMENDATIONS

### Immediate Actions (Critical)

1. **Replace all hardcoded URLs in frontend**
   ```javascript
   // Bad
   fetch('http://localhost:8000/api/articles')
   
   // Good
   import axios from '../utils/axiosConfig';
   axios.get('/api/articles')
   ```

2. **Remove hardcoded CORS headers from backend**
   - Delete inline CORS headers in api.php
   - Rely on config/cors.php
   - Remove manual OPTIONS routes

3. **Remove debug/test endpoints**
   - `/test-drafts`
   - `/test-publish/{id}`
   - `/storage-link`
   - `/admin/reset-data` (or add confirmation + logging)

4. **Fix unreachable code in `/latest-articles` route**

### Short-term Improvements

5. **Standardize API response format**
   ```php
   // Consistent format
   return response()->json([
       'data' => $data,
       'meta' => [...],
       'links' => [...]
   ]);
   ```

6. **Add frontend validation matching backend rules**
   - Title max length: 255
   - Image max size: 5MB
   - Required fields

7. **Centralize error handling**
   ```javascript
   // Create utils/errorHandler.js
   export const handleApiError = (error) => {
       if (error.response?.status === 401) {
           // Handle unauthorized
       }
       // ... other cases
   };
   ```

8. **Protect log endpoints**
   ```php
   Route::middleware('auth:sanctum')->group(function () {
       Route::get('/logs', [LogController::class, 'index']);
       Route::get('/logs/{log}', [LogController::class, 'show']);
   });
   ```

### Long-term Improvements

9. **Remove duplicate API configuration**
   - Delete `utils/api.js` or consolidate with axiosConfig.js
   - Update all imports

10. **Implement API versioning**
    ```php
    Route::prefix('v1')->group(function () {
        // All API routes
    });
    ```

11. **Add request/response logging middleware**

12. **Implement comprehensive API documentation** (Swagger/OpenAPI)

13. **Add API rate limiting per user** (not just IP)

14. **Implement proper API error codes and messages**
    ```php
    return response()->json([
        'error' => [
            'code' => 'ARTICLE_NOT_FOUND',
            'message' => 'The requested article does not exist',
            'status' => 404
        ]
    ], 404);
    ```

---

## 📊 SUMMARY

### Critical Issues: 3
- Hardcoded URLs in frontend
- Hardcoded CORS headers
- Inconsistent API configuration

### Major Issues: 4
- Missing auth on test endpoints
- Unreachable code
- Inconsistent error handling
- Missing frontend validation

### Minor Issues: 6
- Mixed API call patterns
- Duplicate CORS handling
- Inconsistent response formats
- Weak rate limiting
- XSS concerns
- Exposed log endpoints

### Overall Assessment
**Grade: C+ (Functional but needs refactoring)**

The application is functional and has good security foundations (Sanctum auth, role-based access, email verification). However, it suffers from:
- **Inconsistent patterns** (multiple ways to do the same thing)
- **Environment-specific hardcoding** (will break in production)
- **Technical debt** (dead code, unused utilities, duplicate implementations)

### Priority Actions
1. Fix hardcoded URLs (blocks deployment)
2. Fix CORS configuration (blocks deployment)
3. Remove test endpoints (security risk)
4. Standardize API calls (maintainability)
5. Add proper validation (user experience)

---

**Audit Date:** 2024
**Auditor:** Senior Developer Review
**Project:** La Verdad Herald News Platform
