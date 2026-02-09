# Registration Testing Guide

## ✅ Registration Endpoint Analysis

**Endpoint:** `POST /api/register`
**Location:** `backend/routes/api.php` (line 27)
**Controller:** `AuthController::registerApi`

### What It Does:
1. Validates input (name, email, password)
2. Creates user with role `'user'`
3. Auto-verifies email (until Brevo SMTP configured)
4. Returns 201 success message

### Password Requirements:
- Minimum 8 characters
- At least 1 lowercase letter
- At least 1 uppercase letter
- At least 1 number
- Must match confirmation

## 🧪 How to Test:

### Step 1: Start Backend
```bash
cd backend
php artisan serve
```

### Step 2: Test with cURL
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d "{\"name\":\"John Doe\",\"email\":\"john@example.com\",\"password\":\"Password123\",\"password_confirmation\":\"Password123\"}"
```

### Step 3: Expected Response
```json
{
  "message": "Registration successful. You can now log in."
}
```
**Status Code:** 201

### Step 4: Verify in Database
```bash
php artisan tinker
>>> User::where('email', 'john@example.com')->first()
```

## 🎯 Test Cases:

### ✅ Valid Registration
```json
{
  "name": "Test User",
  "email": "test@example.com",
  "password": "Password123",
  "password_confirmation": "Password123"
}
```
**Expected:** 201 Created

### ❌ Duplicate Email
```json
{
  "name": "Another User",
  "email": "test@example.com",
  "password": "Password123",
  "password_confirmation": "Password123"
}
```
**Expected:** 422 Validation Error

### ❌ Weak Password
```json
{
  "name": "Test User",
  "email": "weak@example.com",
  "password": "password",
  "password_confirmation": "password"
}
```
**Expected:** 422 Validation Error (no uppercase/number)

### ❌ Password Mismatch
```json
{
  "name": "Test User",
  "email": "mismatch@example.com",
  "password": "Password123",
  "password_confirmation": "Password456"
}
```
**Expected:** 422 Validation Error

## 🔍 What Gets Created:

```sql
INSERT INTO users (name, email, password, role, email_verified_at)
VALUES ('Test User', 'test@example.com', '$2y$...', 'user', NOW());
```

## 🚀 Frontend Integration:

The frontend should call:
```javascript
const response = await fetch('http://localhost:8000/api/register', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    name: 'John Doe',
    email: 'john@example.com',
    password: 'Password123',
    password_confirmation: 'Password123'
  })
});
```

## ✅ Registration Flow:

1. User fills form → Frontend validates
2. Frontend sends POST to `/api/register`
3. Backend validates & creates user
4. User auto-verified (email_verified_at = now)
5. Success message returned
6. User can immediately login

## 🔒 Security Features:

- ✅ Rate limiting (5 requests per minute)
- ✅ Password hashing (bcrypt)
- ✅ Email uniqueness check
- ✅ Strong password validation
- ✅ CORS protection
- ✅ Auto email verification (temporary)

## 📝 Notes:

- Email verification is **disabled** until Brevo SMTP is configured
- Users are auto-verified on registration
- Default role is `'user'`
- No author record created (must be added by admin)
