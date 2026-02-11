# Brevo SMTP Setup for Production

## Why Brevo?
- ✅ 300 emails/day FREE
- ✅ Professional email service
- ✅ High deliverability
- ✅ Easy setup
- ✅ No credit card required

## Step 1: Create Brevo Account

1. Go to: https://www.brevo.com
2. Click "Sign up free"
3. Enter your email and create account
4. Verify your email address

## Step 2: Get SMTP Credentials

1. Login to Brevo dashboard
2. Click your name (top right) → "SMTP & API"
3. Click "SMTP" tab
4. You'll see:
   - **SMTP Server**: `smtp-relay.brevo.com`
   - **Port**: `587`
   - **Login**: Your email address
   - **SMTP Key**: Click "Create a new SMTP key"

5. Name it "La Verdad Herald" and click "Generate"
6. Copy the SMTP key (looks like: `xsmtpsib-a1b2c3d4...`)

## Step 3: Add Sender Email

1. Go to "Senders" in left menu
2. Click "Add a sender"
3. Enter: `noreply@laverdad.edu.ph`
4. Verify the email (check inbox for verification link)

## Step 4: Update Production .env

Update `backend/.env` with:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your_brevo_email@example.com
MAIL_PASSWORD=xsmtpsib-your_smtp_key_here
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@laverdad.edu.ph"
MAIL_FROM_NAME="La Verdad Herald"
```

**Replace:**
- `your_brevo_email@example.com` - Email you used to sign up
- `xsmtpsib-your_smtp_key_here` - The SMTP key you generated
- `noreply@laverdad.edu.ph` - Must be verified in Brevo

## Step 5: Test Email

Run in `backend` folder:

```bash
php artisan config:clear
php artisan tinker
```

Then:

```php
Mail::raw('Production test from La Verdad Herald', function($message) {
    $message->to('test@laverdad.edu.ph')
            ->subject('Production Email Test');
});
```

## Step 6: Deploy to Production

### For Railway (Backend):

1. Go to Railway dashboard
2. Select your project
3. Click "Variables" tab
4. Add these variables:

```
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your_brevo_email@example.com
MAIL_PASSWORD=xsmtpsib-your_smtp_key_here
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@laverdad.edu.ph
MAIL_FROM_NAME=La Verdad Herald
```

5. Click "Deploy" to restart with new config

### For Vercel (Frontend):

No changes needed - email is handled by backend.

## Email Limits

**Free Plan:**
- 300 emails per day
- Unlimited contacts
- Email support

**If you need more:**
- Starter: $25/month (20,000 emails/month)
- Business: $65/month (100,000 emails/month)

## Monitoring Emails

1. Go to Brevo dashboard
2. Click "Statistics" → "Email"
3. See:
   - Emails sent
   - Delivery rate
   - Open rate
   - Bounce rate

## Troubleshooting

**Error: "Authentication failed"**
- Verify SMTP key is correct
- Check MAIL_USERNAME matches Brevo account email

**Error: "Sender not verified"**
- Go to Brevo → Senders
- Verify the sender email address
- Check verification email in inbox

**Emails going to spam**
- Add SPF record to domain DNS
- Add DKIM record (provided by Brevo)
- Warm up sender reputation (start with few emails)

## Domain Authentication (Optional but Recommended)

1. Go to Brevo → "Senders" → "Domains"
2. Add `laverdad.edu.ph`
3. Add DNS records provided by Brevo:
   - SPF record
   - DKIM record
   - DMARC record

This improves email deliverability and prevents spam filtering.

## Best Practices

1. **Use verified sender**: Always use verified email in MAIL_FROM_ADDRESS
2. **Monitor limits**: Check daily usage in Brevo dashboard
3. **Handle bounces**: Remove invalid emails from database
4. **Test first**: Always test in staging before production
5. **Backup service**: Have Gmail SMTP as backup if Brevo fails

## Support

- Brevo Help: https://help.brevo.com
- Email: support@brevo.com
- Live chat available in dashboard
