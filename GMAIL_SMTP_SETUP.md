# Gmail SMTP Email Setup Guide

## Step 1: Get Gmail App Password

1. Go to your Google Account: https://myaccount.google.com/
2. Click "Security" in left menu
3. Enable "2-Step Verification" (if not enabled)
4. Search for "App passwords" or go to: https://myaccount.google.com/apppasswords
5. Select "Mail" and "Other (Custom name)"
6. Name it "La Verdad Herald"
7. Click "Generate"
8. Copy the 16-character password (example: `abcd efgh ijkl mnop`)

## Step 2: Update .env File

Replace the MAIL section in `backend/.env` with:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=abcdefghijklmnop
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your_email@gmail.com"
MAIL_FROM_NAME="La Verdad Herald"
```

**Replace:**
- `your_email@gmail.com` with your actual Gmail address
- `abcdefghijklmnop` with the 16-character app password (no spaces)

## Step 3: Test Email Sending

Run this command in `backend` folder:

```bash
php artisan tinker
```

Then run:

```php
Mail::raw('Test email from La Verdad Herald', function($message) {
    $message->to('test@laverdad.edu.ph')
            ->subject('Test Email');
});
```

If successful, you'll see: `= Illuminate\Mail\SentMessage`

## Step 4: Clear Config Cache

```bash
php artisan config:clear
php artisan cache:clear
```

## Alternative: Mailtrap (For Testing Only)

If you want to test without sending real emails:

1. Sign up: https://mailtrap.io
2. Get SMTP credentials from inbox settings
3. Update .env:

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@laverdad.edu.ph"
MAIL_FROM_NAME="La Verdad Herald"
```

## Troubleshooting

**Error: "Failed to authenticate"**
- Make sure 2-Step Verification is enabled
- Use App Password, not regular Gmail password
- Remove spaces from app password

**Error: "Connection timeout"**
- Check firewall/antivirus blocking port 587
- Try port 465 with `MAIL_ENCRYPTION=ssl`

**Emails not arriving**
- Check spam folder
- Verify MAIL_FROM_ADDRESS matches MAIL_USERNAME
- Wait a few minutes (Gmail may delay first emails)

## Production Setup

For production, use a professional email service:
- **Brevo** (300 emails/day free): https://www.brevo.com
- **SendGrid** (100 emails/day free): https://sendgrid.com
- **Mailgun** (5000 emails/month free): https://www.mailgun.com
