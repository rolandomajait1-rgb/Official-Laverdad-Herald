# 🧪 QA Test Plan - La Verdad Herald System

## Test Environment
- **Backend API**: http://localhost:8000
- **Frontend**: http://localhost:5174
- **Database**: SQLite (local)
- **Test Date**: 2026-02-16

---

## 1️⃣ USER REGISTRATION FLOW

### Test Case 1.1: Valid Registration
**Endpoint**: `POST /api/register`

**Test Data**:
```json
{
  "name": "Test User QA",
  "email": "testqa@student.laverdad.edu.ph",
  "password": "Password123",
  "password_confirmation": "Password123"
}
```

**Expected Results**:
- ✅ Status: 201 Created
- ✅ Response contains: user object, message
- ✅ User created in database
- ✅ email_verified_at is NULL
- ✅ Verification email sent

**Actual Results**: [TO BE TESTED]

---

### Test Case 1.2: Invalid Email Domain
**Test Data**:
```json
{
  "name": "Test User",
  "email": "test@gmail.com",
  "password": "Password123",
  "password_confirmation": "Password123"
}
```

**Expected Results**:
- ✅ Status: 422 Unprocessable Entity
- ✅ Error message: "email must end with @student.laverdad.edu.ph"

**Actual Results**: [TO BE TESTED]

---

### Test Case 1.3: Weak Password
**Test Data**:
```json
{
  "name": "Test User",
  "email": "test@student.laverdad.edu.ph",
  "password": "weak",
  "password_confirmation": "weak"
}
```

**Expected Results**:
- ✅ Status: 422
- ✅ Error: Password must be 8+ chars with uppercase, lowercase, numbers

**Actual Results**: [TO BE TESTED]

---

### Test Case 1.4: Duplicate Email
**Test Data**: Use existing email

**Expected Results**:
- ✅ Status: 422
- ✅ Error: "email has already been taken"

**Actual Results**: [TO BE TESTED]

---

## 2️⃣ EMAIL VERIFICATION FLOW

### Test Case 2.1: Email Sent After Registration
**Prerequisites**: Complete Test Case 1.1

**Expected Results**:
- ✅ Email received in inbox
- ✅ Email contains verification link
- ✅ Link format: `{BACKEND_URL}/api/email/verify/{id}/{hash}`
- ✅ Email from: rolandomajait1@gmail.com
- ✅ Email subject contains "Verify"

**Actual Results**: [TO BE TESTED]

---

### Test Case 2.2: Click Verification Link
**Endpoint**: `GET /api/email/verify/{id}/{hash}`

**Expected Results**:
- ✅ Status: 302 Redirect
- ✅ Redirects to: `{FRONTEND_URL}/email-verified?verified=1`
- ✅ User's email_verified_at updated in database
- ✅ Success page displays

**Actual Results**: [TO BE TESTED]

---

### Test Case 2.3: Already Verified Email
**Prerequisites**: Email already verified

**Expected Results**:
- ✅ Redirects to: `{FRONTEND_URL}/email-verified?verified=1&message=already_verified`
- ✅ Message: "Your email is already verified"

**Actual Results**: [TO BE TESTED]

---

### Test Case 2.4: Invalid Verification Link
**Test Data**: Tampered hash or expired link

**Expected Results**:
- ✅ Redirects to: `{FRONTEND_URL}/login?error=invalid_verification_link`
- ✅ Error message displayed

**Actual Results**: [TO BE TESTED]

---

### Test Case 2.5: Resend Verification Email
**Endpoint**: `POST /api/email/resend-verification`

**Test Data**:
```json
{
  "email": "testqa@student.laverdad.edu.ph"
}
```

**Expected Results**:
- ✅ Status: 200 OK
- ✅ New verification email sent
- ✅ Rate limited: 3 requests per minute

**Actual Results**: [TO BE TESTED]

---

## 3️⃣ USER LOGIN FLOW

### Test Case 3.1: Login Before Email Verification
**Endpoint**: `POST /api/login`

**Test Data**:
```json
{
  "email": "testqa@student.laverdad.edu.ph",
  "password": "Password123"
}
```

**Expected Results**:
- ✅ Status: 403 Forbidden
- ✅ Message: "Please verify your email before logging in"
- ✅ requires_verification: true
- ✅ Show resend verification button

**Actual Results**: [TO BE TESTED]

---

### Test Case 3.2: Valid Login (After Verification)
**Prerequisites**: Email verified

**Expected Results**:
- ✅ Status: 200 OK
- ✅ Response contains: token, role, user object
- ✅ Token stored in localStorage
- ✅ Redirect based on role (user → /home, admin → /admin)

**Actual Results**: [TO BE TESTED]

---

### Test Case 3.3: Invalid Credentials
**Test Data**: Wrong password

**Expected Results**:
- ✅ Status: 401 Unauthorized
- ✅ Message: "Invalid credentials"
- ✅ No timing attack vulnerability

**Actual Results**: [TO BE TESTED]

---

### Test Case 3.4: Non-existent User
**Test Data**: Email not in database

**Expected Results**:
- ✅ Status: 401
- ✅ Message: "Invalid credentials"
- ✅ Same response time as wrong password

**Actual Results**: [TO BE TESTED]

---

## 4️⃣ PASSWORD RESET FLOW

### Test Case 4.1: Request Password Reset
**Endpoint**: `POST /api/forgot-password`

**Test Data**:
```json
{
  "email": "testqa@student.laverdad.edu.ph"
}
```

**Expected Results**:
- ✅ Status: 200 OK
- ✅ Reset email sent
- ✅ Email contains reset link
- ✅ Token expires in 24 hours

**Actual Results**: [TO BE TESTED]

---

### Test Case 4.2: Reset Password with Valid Token
**Endpoint**: `POST /api/reset-password`

**Test Data**:
```json
{
  "token": "{token_from_email}",
  "email": "testqa@student.laverdad.edu.ph",
  "password": "NewPassword123",
  "password_confirmation": "NewPassword123"
}
```

**Expected Results**:
- ✅ Status: 200 OK
- ✅ Password updated in database
- ✅ All tokens revoked
- ✅ Redirect to login

**Actual Results**: [TO BE TESTED]

---

### Test Case 4.3: Expired Reset Token
**Test Data**: Token older than 24 hours

**Expected Results**:
- ✅ Status: 422
- ✅ Error: "Token expired or invalid"

**Actual Results**: [TO BE TESTED]

---

## 5️⃣ AUTHENTICATED USER ENDPOINTS

### Test Case 5.1: Access Protected Route Without Token
**Endpoint**: `GET /api/user/profile`

**Expected Results**:
- ✅ Status: 401 Unauthorized
- ✅ Message: "Unauthenticated"

**Actual Results**: [TO BE TESTED]

---

### Test Case 5.2: Access Protected Route With Valid Token
**Headers**: `Authorization: Bearer {token}`

**Expected Results**:
- ✅ Status: 200 OK
- ✅ User data returned

**Actual Results**: [TO BE TESTED]

---

### Test Case 5.3: Logout
**Endpoint**: `POST /api/logout`

**Expected Results**:
- ✅ Status: 200 OK
- ✅ Token revoked
- ✅ Subsequent requests with token fail

**Actual Results**: [TO BE TESTED]

---

## 6️⃣ ADMIN ROLE TESTS

### Test Case 6.1: Admin User Login
**Test Data**: Admin credentials

**Expected Results**:
- ✅ Status: 200 OK
- ✅ role: "admin"
- ✅ Redirect to /admin

**Actual Results**: [TO BE TESTED]

---

### Test Case 6.2: Access Admin-Only Endpoint as User
**Endpoint**: `GET /api/admin/statistics`
**Headers**: User token

**Expected Results**:
- ✅ Status: 403 Forbidden
- ✅ Message: "Unauthorized"

**Actual Results**: [TO BE TESTED]

---

### Test Case 6.3: Access Admin-Only Endpoint as Admin
**Headers**: Admin token

**Expected Results**:
- ✅ Status: 200 OK
- ✅ Statistics data returned

**Actual Results**: [TO BE TESTED]

---

## 7️⃣ ARTICLE ENDPOINTS

### Test Case 7.1: Get All Articles (Public)
**Endpoint**: `GET /api/articles`

**Expected Results**:
- ✅ Status: 200 OK
- ✅ Paginated response
- ✅ Only published articles
- ✅ No authentication required

**Actual Results**: [TO BE TESTED]

---

### Test Case 7.2: Get Article by Slug
**Endpoint**: `GET /api/articles/by-slug/{slug}`

**Expected Results**:
- ✅ Status: 200 OK
- ✅ Article with categories, tags, author
- ✅ View count incremented

**Actual Results**: [TO BE TESTED]

---

### Test Case 7.3: Create Article (Admin Only)
**Endpoint**: `POST /api/articles`
**Headers**: Admin token

**Test Data**:
```json
{
  "title": "Test Article",
  "content": "Test content",
  "excerpt": "Test excerpt",
  "status": "published",
  "category_ids": [1],
  "tag_ids": [1, 2]
}
```

**Expected Results**:
- ✅ Status: 201 Created
- ✅ Article created with slug
- ✅ Categories and tags attached

**Actual Results**: [TO BE TESTED]

---

## 8️⃣ CATEGORY ENDPOINTS

### Test Case 8.1: Get All Categories
**Endpoint**: `GET /api/categories`

**Expected Results**:
- ✅ Status: 200 OK
- ✅ List of all categories
- ✅ Includes article count

**Actual Results**: [TO BE TESTED]

---

### Test Case 8.2: Get Articles by Category
**Endpoint**: `GET /api/categories/{slug}/articles`

**Expected Results**:
- ✅ Status: 200 OK
- ✅ Only articles in that category
- ✅ Paginated

**Actual Results**: [TO BE TESTED]

---

## 9️⃣ SEARCH FUNCTIONALITY

### Test Case 9.1: Search Articles
**Endpoint**: `GET /api/search?q=test`

**Expected Results**:
- ✅ Status: 200 OK
- ✅ Articles matching query
- ✅ Searches title, content, excerpt

**Actual Results**: [TO BE TESTED]

---

### Test Case 9.2: Empty Search Query
**Endpoint**: `GET /api/search?q=`

**Expected Results**:
- ✅ Status: 200 OK
- ✅ Empty results or validation error

**Actual Results**: [TO BE TESTED]

---

## 🔟 RATE LIMITING TESTS

### Test Case 10.1: Login Rate Limit
**Test**: 6 login attempts in 1 minute

**Expected Results**:
- ✅ First 5 attempts: Normal response
- ✅ 6th attempt: 429 Too Many Requests
- ✅ Message: "Too many login attempts"

**Actual Results**: [TO BE TESTED]

---

### Test Case 10.2: Email Resend Rate Limit
**Test**: 4 resend requests in 1 minute

**Expected Results**:
- ✅ First 3 attempts: Success
- ✅ 4th attempt: 429 Too Many Requests

**Actual Results**: [TO BE TESTED]

---

## 1️⃣1️⃣ SECURITY TESTS

### Test Case 11.1: SQL Injection
**Test Data**: `' OR '1'='1` in email field

**Expected Results**:
- ✅ No SQL injection vulnerability
- ✅ Proper error handling

**Actual Results**: [TO BE TESTED]

---

### Test Case 11.2: XSS Attack
**Test Data**: `<script>alert('xss')</script>` in article content

**Expected Results**:
- ✅ Content sanitized
- ✅ Script not executed

**Actual Results**: [TO BE TESTED]

---

### Test Case 11.3: CSRF Protection
**Test**: Submit form without CSRF token

**Expected Results**:
- ✅ Request rejected
- ✅ 419 Page Expired (for web routes)

**Actual Results**: [TO BE TESTED]

---

## 1️⃣2️⃣ CORS TESTS

### Test Case 12.1: Valid Origin
**Origin**: https://official-laverdad-herald.vercel.app

**Expected Results**:
- ✅ Request allowed
- ✅ CORS headers present

**Actual Results**: [TO BE TESTED]

---

### Test Case 12.2: Invalid Origin
**Origin**: https://malicious-site.com

**Expected Results**:
- ✅ Request blocked
- ✅ No CORS headers

**Actual Results**: [TO BE TESTED]

---

## 📊 TEST SUMMARY

| Category | Total Tests | Passed | Failed | Blocked |
|----------|-------------|--------|--------|---------|
| Registration | 4 | - | - | - |
| Email Verification | 5 | - | - | - |
| Login | 4 | - | - | - |
| Password Reset | 3 | - | - | - |
| Authentication | 3 | - | - | - |
| Admin | 3 | - | - | - |
| Articles | 3 | - | - | - |
| Categories | 2 | - | - | - |
| Search | 2 | - | - | - |
| Rate Limiting | 2 | - | - | - |
| Security | 3 | - | - | - |
| CORS | 2 | - | - | - |
| **TOTAL** | **36** | **0** | **0** | **0** |

---

## 🚀 EXECUTION PLAN

1. Start backend server: `php artisan serve`
2. Start frontend server: `npm run dev`
3. Clear database: `php artisan migrate:fresh --seed`
4. Execute tests in order
5. Document results
6. Report bugs

---

## 🐛 BUG TRACKING

### Critical Bugs:
- [ ] None found yet

### High Priority:
- [ ] None found yet

### Medium Priority:
- [ ] None found yet

### Low Priority:
- [ ] None found yet

---

## ✅ SIGN-OFF

**Tested By**: QA Senior Engineer
**Date**: 2026-02-16
**Status**: READY TO TEST
**Environment**: Local Development

