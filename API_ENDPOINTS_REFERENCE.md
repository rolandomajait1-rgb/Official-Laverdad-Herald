# API Endpoints Quick Reference

## Base URL
- **Local:** `http://localhost:8000`
- **Production:** `https://official-laverdad-herald.onrender.com`

---

## PUBLIC ENDPOINTS (No Auth Required)

### Articles

```
GET  /api/articles/public
     ?limit=10&page=1
     Returns: Paginated published articles

GET  /api/latest-articles
     Returns: Latest 6 published articles

GET  /api/articles/search
     ?q=keyword
     Returns: Articles matching search query (min 3 chars)

GET  /api/articles/by-slug/{slug}
     Returns: Single article by slug

GET  /api/articles/id/{id}
     Returns: Single article by ID

GET  /api/articles
     ?category=news&limit=10&page=1
     Returns: Articles filtered by category (case-insensitive)
```

### Categories

```
GET  /api/categories
     Returns: All categories

GET  /api/categories/{category}/articles
     Returns: Articles in specific category (case-insensitive)
     Examples: /api/categories/news/articles
               /api/categories/sports/articles
```

### Authors

```
GET  /api/authors
     Returns: All authors

GET  /api/authors/{authorName}
     Returns: Author profile and their articles

GET  /api/articles/author-public/{authorId}
     ?per_page=10&page=1
     Returns: Articles by specific author
```

### Tags

```
GET  /api/tags
     Returns: All tags

GET  /api/tags/{tag}
     Returns: Tag details and articles
```

### Team & Contact

```
GET  /api/team-members
     Returns: Team member list

POST /api/contact/subscribe
     Body: { email }
     Returns: Subscription confirmation

POST /api/contact/feedback
     Body: { name, email, message }
     Returns: Feedback submission confirmation

POST /api/contact/request-coverage
     Body: { name, email, event, date, location, details }
     Returns: Coverage request confirmation

POST /api/contact/join-herald
     Body: { name, email, phone, position, experience, portfolio }
     Returns: Application submission confirmation
```

---

## AUTHENTICATION ENDPOINTS

### Auth

```
POST /api/login
     Body: { email, password }
     Returns: { user, token }

POST /api/register
     Body: { name, email, password, password_confirmation }
     Returns: { user, message }

POST /api/logout
     Headers: Authorization: Bearer {token}
     Returns: { message }

POST /api/forgot-password
     Body: { email }
     Returns: { message }

POST /api/reset-password
     Body: { email, token, password, password_confirmation }
     Returns: { message }

POST /api/change-password
     Headers: Authorization: Bearer {token}
     Body: { current_password, new_password, new_password_confirmation }
     Returns: { message }

POST /api/delete-account
     Headers: Authorization: Bearer {token}
     Body: { password }
     Returns: { message }
```

### Email Verification

```
GET  /api/email/verify-token
     ?token={token}
     Returns: Verification status

GET  /api/email/verify/{id}/{hash}
     Returns: Verification redirect

POST /api/email/resend-verification
     Body: { email }
     Returns: { message }
```

---

## PROTECTED ENDPOINTS (Requires Auth Token)

### User Profile

```
GET  /api/user
     Headers: Authorization: Bearer {token}
     Returns: Current user data

GET  /api/user/liked-articles
     Headers: Authorization: Bearer {token}
     ?per_page=10&page=1
     Returns: User's liked articles

GET  /api/user/shared-articles
     Headers: Authorization: Bearer {token}
     ?per_page=10&page=1
     Returns: User's shared articles
```

### Article Management (Admin/Moderator)

```
GET  /api/articles
     Headers: Authorization: Bearer {token}
     ?status=draft&category=news&limit=10
     Returns: All articles (including drafts for admins)

POST /api/articles
     Headers: Authorization: Bearer {token}
              Content-Type: multipart/form-data
     Body: FormData {
       title,
       content,
       category_id,
       tags[] (array),
       author_name,
       featured_image (file),
       status (published|draft)
     }
     Returns: Created article

GET  /api/articles/{id}
     Headers: Authorization: Bearer {token}
     Returns: Single article with full details

PUT  /api/articles/{id}
     Headers: Authorization: Bearer {token}
     Body: {
       title,
       content,
       category,
       tags (comma-separated),
       author,
       featured_image (optional),
       status (optional)
     }
     Returns: Updated article

DELETE /api/articles/{id}
     Headers: Authorization: Bearer {token}
     Returns: { message }

POST /api/articles/{id}/like
     Headers: Authorization: Bearer {token}
     Returns: { liked: boolean, likes_count: number }

GET  /api/articles/author/{authorId}
     Headers: Authorization: Bearer {token}
     ?per_page=10&page=1&status=published
     Returns: Articles by author
```

---

## ADMIN ENDPOINTS (Admin Only)

### Dashboard

```
GET  /api/admin/dashboard-stats
     Headers: Authorization: Bearer {token}
     Returns: {
       users: number,
       articles: number,
       drafts: number,
       views: number,
       likes: number
     }

GET  /api/admin/recent-activity
     Headers: Authorization: Bearer {token}
     Returns: Array of recent activities

GET  /api/admin/audit-logs
     Headers: Authorization: Bearer {token}
     Returns: Array of audit log entries

GET  /api/admin/stats
     Headers: Authorization: Bearer {token}
     Returns: {
       totalArticles,
       totalUsers,
       totalViews,
       recentArticles
     }

GET  /api/admin/check-access
     Headers: Authorization: Bearer {token}
     Returns: { is_admin: boolean }
```

### Moderator Management

```
GET  /api/admin/moderators
     Headers: Authorization: Bearer {token}
     Returns: Array of moderator users

POST /api/admin/moderators
     Headers: Authorization: Bearer {token}
     Body: { email }
     Returns: Updated moderator

DELETE /api/admin/moderators/{id}
     Headers: Authorization: Bearer {token}
     Returns: { message }
```

### User Management

```
GET    /api/admin/users
       Headers: Authorization: Bearer {token}
       Returns: All users

POST   /api/admin/users
       Headers: Authorization: Bearer {token}
       Body: { name, email, password, role }
       Returns: Created user

GET    /api/admin/users/{id}
       Headers: Authorization: Bearer {token}
       Returns: Single user

PUT    /api/admin/users/{id}
       Headers: Authorization: Bearer {token}
       Body: { name, email, role }
       Returns: Updated user

DELETE /api/admin/users/{id}
       Headers: Authorization: Bearer {token}
       Returns: { message }
```

### Category Management

```
POST   /api/categories
       Headers: Authorization: Bearer {token}
       Body: { name, description }
       Returns: Created category

GET    /api/categories/{id}
       Headers: Authorization: Bearer {token}
       Returns: Category details

PUT    /api/categories/{id}
       Headers: Authorization: Bearer {token}
       Body: { name, description }
       Returns: Updated category

DELETE /api/categories/{id}
       Headers: Authorization: Bearer {token}
       Returns: { message }
```

### Tag Management

```
POST   /api/tags
       Headers: Authorization: Bearer {token}
       Body: { name }
       Returns: Created tag

PUT    /api/tags/{id}
       Headers: Authorization: Bearer {token}
       Body: { name }
       Returns: Updated tag

DELETE /api/tags/{id}
       Headers: Authorization: Bearer {token}
       Returns: { message }
```

---

## MODERATOR ENDPOINTS (Admin + Moderator)

### Draft Management

```
GET    /api/drafts
       Headers: Authorization: Bearer {token}
       Returns: All draft articles

POST   /api/drafts
       Headers: Authorization: Bearer {token}
       Body: { title, content, category_id, tags, author_name }
       Returns: Created draft

GET    /api/drafts/{id}
       Headers: Authorization: Bearer {token}
       Returns: Single draft

PUT    /api/drafts/{id}
       Headers: Authorization: Bearer {token}
       Body: { title, content, category_id, tags, status }
       Returns: Updated draft

DELETE /api/drafts/{id}
       Headers: Authorization: Bearer {token}
       Returns: { message }
```

---

## RATE LIMITS

| Endpoint Group | Limit |
|----------------|-------|
| Login/Register | 5 requests per minute |
| Password Reset | 5 requests per minute |
| Email Verification Resend | 3 requests per minute |
| Contact Forms | 5-10 requests per minute |

---

## ERROR RESPONSES

### 400 Bad Request
```json
{
  "error": "Invalid request data",
  "errors": {
    "field": ["Validation error message"]
  }
}
```

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
  "error": "Admin access required"
}
```

### 404 Not Found
```json
{
  "error": "Resource not found"
}
```

### 422 Unprocessable Entity
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field": ["Validation error message"]
  }
}
```

### 429 Too Many Requests
```json
{
  "message": "Too many requests. Please try again later."
}
```

### 500 Internal Server Error
```json
{
  "error": "Internal server error",
  "message": "Error details"
}
```

---

## AUTHENTICATION FLOW

1. **Register:** `POST /api/register`
2. **Verify Email:** Click link in email → `GET /api/email/verify/{id}/{hash}`
3. **Login:** `POST /api/login` → Receive token
4. **Use Token:** Include in all protected requests: `Authorization: Bearer {token}`
5. **Logout:** `POST /api/logout`

---

## ARTICLE CREATION FLOW

1. **Fetch Categories:** `GET /api/categories`
2. **Create Article:** `POST /api/articles` with FormData
3. **Article Created:** Returns article with ID
4. **View Article:** `GET /api/articles/by-slug/{slug}`

---

## CATEGORY FILTERING

All category endpoints support case-insensitive matching:
- `news`, `News`, `NEWS` → All match "News" category
- `sports`, `Sports`, `SPORTS` → All match "Sports" category

---

## PAGINATION

Paginated endpoints return:
```json
{
  "data": [...],
  "current_page": 1,
  "last_page": 5,
  "per_page": 10,
  "total": 50,
  "links": {
    "first": "url",
    "last": "url",
    "prev": null,
    "next": "url"
  }
}
```

---

## NOTES

- All timestamps are in UTC
- File uploads limited to 5MB
- Images must be JPEG, PNG, or JPG format
- Email domain must be `@student.laverdad.edu.ph` for registration
- Password must be 8+ characters with uppercase, lowercase, and numbers
