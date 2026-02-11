# Email Verification Setup Guide

## Current Status
✅ Email verification is ALREADY IMPLEMENTED in the code!

## How It Works

### Registration Flow:
1. User registers with @laverdad.edu.ph email
2. System automatically sends verification email
3. User receives email with verification link
4. User clicks link to verify email
5. User can now login

### Login Protection:
- Unverified users cannot login
- Error message: "Please verify your email before logging in"

## Email Configuration Required

Update these values in `backend/.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your_actual_email@laverdad.edu.ph
MAIL_PASSWORD=your_actual_smtp_password
MAIL_FROM_ADDRESS="noreply@laverdad.edu.ph"
MAIL_FROM_NAME="La Verdad Herald"
```

## Recommended Email Services

### Option 1: Brevo (Free - Already configured)
- Sign up: https://www.brevo.com
- Get SMTP credentials
- Free: 300 emails/day

### Option 2: Gmail SMTP
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@laverdad.edu.ph
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
```

### Option 3: Mailtrap (Testing Only)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
```

## Testing Email Verification

1. Configure email in `.env`
2. Register new user with @laverdad.edu.ph email
3. Check email inbox for verification link
4. Click verification link
5. Try to login - should work!

## API Endpoints

- `POST /api/register` - Sends verification email
- `GET /api/email/verify/{id}/{hash}` - Verifies email
- `POST /api/email/resend-verification` - Resends verification email
- `POST /api/login` - Blocks unverified users

## Frontend Integration

The frontend should:
1. Show "Check your email" message after registration
2. Show "Resend verification email" button
3. Handle verification success/error messages
