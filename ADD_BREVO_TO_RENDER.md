# Add Brevo Email to Render - Step by Step

## Step 1: Get Brevo SMTP Credentials

1. Go to: https://app.brevo.com
2. Login to your account
3. Click your name (top right) → "SMTP & API"
4. Click "SMTP" tab
5. You'll see:
   - **SMTP Server**: `smtp-relay.brevo.com`
   - **Port**: `587`
   - **Login**: Your Brevo account email
6. Click "Create a new SMTP key"
7. Name it: "La Verdad Herald"
8. Copy the key (starts with `xsmtpsib-`)

## Step 2: Verify Sender Email in Brevo

1. Go to "Senders" in left menu
2. Click "Add a sender"
3. Enter your email (e.g., `your_email@gmail.com`)
4. Click "Add"
5. Check your email inbox
6. Click verification link
7. Wait for "Verified" status

## Step 3: Add to Render Environment Variables

1. Go to: https://dashboard.render.com
2. Select your backend service
3. Click "Environment" tab
4. Click "Add Environment Variable"
5. Add these ONE BY ONE:

```
MAIL_MAILER
smtp

MAIL_HOST
smtp-relay.brevo.com

MAIL_PORT
587

MAIL_USERNAME
your_brevo_account_email@example.com

MAIL_PASSWORD
xsmtpsib-your_smtp_key_here

MAIL_ENCRYPTION
tls

MAIL_FROM_ADDRESS
your_verified_email@gmail.com

MAIL_FROM_NAME
La Verdad Herald
```

**Important:**
- `MAIL_USERNAME` = Email you used to sign up for Brevo
- `MAIL_PASSWORD` = The SMTP key (starts with `xsmtpsib-`)
- `MAIL_FROM_ADDRESS` = Email you verified in Step 2

## Step 4: Save and Deploy

1. Click "Save Changes"
2. Render will automatically redeploy
3. Wait 2-3 minutes for deployment

## Step 5: Test Email

After deployment completes:

1. Go to your frontend
2. Register new user with `@laverdad.edu.ph` email
3. Check email inbox
4. You should receive verification email!

## Example Configuration

If you signed up with `john@gmail.com` and verified it:

```
MAIL_MAILER = smtp
MAIL_HOST = smtp-relay.brevo.com
MAIL_PORT = 587
MAIL_USERNAME = john@gmail.com
MAIL_PASSWORD = xsmtpsib-abc123def456ghi789
MAIL_ENCRYPTION = tls
MAIL_FROM_ADDRESS = john@gmail.com
MAIL_FROM_NAME = La Verdad Herald
```

## Troubleshooting

**Deployment fails:**
- Check all variables are added correctly
- No typos in variable names
- SMTP key has no spaces

**Email not sending:**
- Check Render logs: Dashboard → Logs
- Verify sender email in Brevo
- Check SMTP key is correct

**Email goes to spam:**
- Normal for first few emails
- Check spam folder
- Mark as "Not Spam"

## Quick Checklist

- [ ] Brevo account created
- [ ] SMTP key generated
- [ ] Sender email verified in Brevo
- [ ] All 8 MAIL variables added to Render
- [ ] Render redeployed successfully
- [ ] Test registration works
- [ ] Verification email received

## Video Tutorial

Watch Render environment variables setup:
https://docs.render.com/configure-environment-variables
