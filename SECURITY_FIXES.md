# Security Configuration - La Verdad Herald

## Access Restriction

**ONLY** users with `@laverdad.edu.ph` email addresses can access the system.

### Domain Restrictions:
1. **Frontend Access**: Only `https://laverdad.edu.ph` (+ `localhost:5173` for dev)
2. **User Access**: Only `@laverdad.edu.ph` email addresses can register/login

## CORS Configuration

All CORS headers are managed through `CorsHelper` class:
- Location: `backend/app/Http/Helpers/CorsHelper.php`
- Allowed Origins: `https://laverdad.edu.ph`, `http://localhost:5173`
- Default Origin: `https://laverdad.edu.ph`

## Email Domain Validation

All authentication endpoints validate email domain:
- Registration: `ends_with:@laverdad.edu.ph`
- Login: `ends_with:@laverdad.edu.ph`
- Error Message: "Only @laverdad.edu.ph email addresses can access this system."

## Security Measures Applied

1. ✅ **Domain Restriction** - Only laverdad.edu.ph can access
2. ✅ **Email Domain Restriction** - Only @laverdad.edu.ph emails allowed
3. ✅ **CORS Whitelisting** - Strict origin validation
4. ✅ **XSS Protection** - DOMPurify sanitization
5. ✅ **Dependency Security** - axios@1.13.5 (0 vulnerabilities)
6. ✅ **Removed Attack Vectors** - Deleted test files with security issues
