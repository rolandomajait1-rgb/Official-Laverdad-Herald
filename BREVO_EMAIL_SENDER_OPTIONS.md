# Brevo Email Sender Options

## ❌ Cannot Use Vercel Domain

You **CANNOT** use `@yourapp.vercel.app` as sender email because:
- Vercel domains are for hosting only, not email
- You don't control Vercel's email servers
- Brevo requires you to verify domain ownership

## ✅ Valid Options for Sender Email

### Option 1: Use Your Personal Email (Easiest)
**Best for testing and small projects**

```env
MAIL_FROM_ADDRESS=your_personal_email@gmail.com
```

**Steps:**
1. Use your Gmail/Yahoo/Outlook email
2. Add it as sender in Brevo
3. Verify by clicking link in email
4. Done! ✅

**Pros:**
- ✅ Free and instant
- ✅ No domain setup needed
- ✅ Works immediately

**Cons:**
- ❌ Looks unprofessional
- ❌ Users see your personal email

---

### Option 2: Use School Domain (Recommended)
**Best for production - looks professional**

```env
MAIL_FROM_ADDRESS=noreply@laverdad.edu.ph
```

**Steps:**
1. Contact your school IT department
2. Ask them to create email: `noreply@laverdad.edu.ph`
3. Get email credentials
4. Add to Brevo as sender
5. Verify email

**Pros:**
- ✅ Professional appearance
- ✅ Matches your website domain
- ✅ Builds trust with users

**Cons:**
- ❌ Requires IT department help
- ❌ May take time to setup

---

### Option 3: Use Free Email Service
**Good compromise - professional looking**

Use a free email service with custom domain:

**Zoho Mail Free:**
- 5GB storage
- 5 users free
- Custom domain support
- Setup: https://www.zoho.com/mail/

**Steps:**
1. Sign up for Zoho Mail
2. Add domain: `laverdad.edu.ph`
3. Verify domain (add DNS records)
4. Create email: `noreply@laverdad.edu.ph`
5. Add to Brevo

```env
MAIL_FROM_ADDRESS=noreply@laverdad.edu.ph
```

---

### Option 4: Use Brevo's Default Domain (Quick Test)
**Only for testing - not recommended for production**

```env
MAIL_FROM_ADDRESS=your_brevo_account@example.com
```

Use the email you signed up with Brevo.

**Pros:**
- ✅ Works immediately
- ✅ No setup needed

**Cons:**
- ❌ Very unprofessional
- ❌ Users see random email
- ❌ Low trust

---

## Recommended Setup Path

### For Development/Testing:
```env
MAIL_FROM_ADDRESS=your_personal_email@gmail.com
MAIL_FROM_NAME="La Verdad Herald (Test)"
```

### For Production:
```env
MAIL_FROM_ADDRESS=noreply@laverdad.edu.ph
MAIL_FROM_NAME="La Verdad Herald"
```

---

## How to Add Sender in Brevo

1. Login to Brevo: https://app.brevo.com
2. Go to "Senders" (left menu)
3. Click "Add a sender"
4. Enter your email address
5. Click "Add"
6. Check your email inbox
7. Click verification link
8. Done! ✅

---

## Email Verification Link Format

When users register, they receive email like:

```
From: noreply@laverdad.edu.ph
Subject: Verify Your Email - La Verdad Herald

Hello!

Please click the link below to verify your email:
https://your-backend.onrender.com/api/email/verify/123/abc123

This link expires in 60 minutes.

Thanks,
La Verdad Herald Team
```

The verification link points to your **backend** (Render), not frontend (Vercel).

---

## Common Questions

**Q: Can I use `@vercel.app` email?**
A: No, Vercel doesn't provide email services.

**Q: Can I use `@onrender.com` email?**
A: No, Render doesn't provide email services.

**Q: Do I need to buy email hosting?**
A: No! Use free options:
- Personal Gmail (testing)
- School email (production)
- Zoho Mail Free (5 users)

**Q: What if I don't have access to school email?**
A: Use your personal email for now, upgrade later.

**Q: Will emails work without custom domain?**
A: Yes! You can use any verified email address.

---

## Quick Start (5 Minutes)

**Use your personal Gmail:**

1. Go to Brevo → Senders
2. Add: `your_email@gmail.com`
3. Verify email
4. Update `.env`:
   ```env
   MAIL_FROM_ADDRESS=your_email@gmail.com
   ```
5. Deploy and test!

**Later, upgrade to school domain when available.**
