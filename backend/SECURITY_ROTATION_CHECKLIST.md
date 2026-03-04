# Security Rotation Checklist

Use this after any secret exposure in chat logs, screenshots, commits, or deployment output.

## 1. Rotate compromised secrets immediately

- Brevo SMTP key: generate a new SMTP key and disable old key.
- Brevo API key (if used): generate new key and disable old key.
- Render PostgreSQL password: rotate database password in Render.
- Cloudinary API secret/key: generate new credentials and disable old ones.
- Laravel `APP_KEY`: rotate only if you accept invalidating encrypted sessions/cookies.

## 2. Update deployment secrets

- Set new values in Render environment variables.
- Keep `MAIL_URL` empty/unset when using SMTP host/port values.
- Set `TRANSACTIONAL_MAIL_MAILER=smtp` for registration and password-reset flows.

## 3. Clear cached config and redeploy

Run in backend service shell:

```bash
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear
```

Then redeploy the service in Render.

## 4. Verify email delivery

Run:

```bash
php artisan email:test-send your-email@example.com
```

Expected result:
- command exits successfully
- email arrives in inbox/spam
- no `Verification email failed` in logs

## 5. Check repository for accidental secrets

From repo root:

```bash
rg -n --hidden --glob "!**/vendor/**" --glob "!**/node_modules/**" "(xsmtpsib-|xkeysib-|CLOUDINARY_API_SECRET=|DATABASE_URL=postgresql://|MAIL_PASSWORD=|APP_KEY=base64:)"
```

Any real credential match must be removed before commit.

## 6. If secrets were pushed to git history

- Rewrite history with `git filter-repo` or BFG.
- Force-push cleaned branches.
- Invalidate all leaked keys anyway.

## 7. Prevention rules

- Never paste real `.env` values in chat.
- Commit only `.env.example` style placeholders.
- Keep local diagnostics scripts ignored in `.gitignore`.
- Use environment variables in scripts instead of hardcoded credentials.
