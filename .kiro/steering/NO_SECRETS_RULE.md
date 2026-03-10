---
inclusion: always
---

# CRITICAL RULE: NEVER COMMIT SECRETS

## Before ANY Git Commit or Push:

### 1. Check for Secrets
Search for these patterns in files to be committed:
- Email addresses (real ones)
- Passwords
- API keys
- SMTP credentials
- Database credentials
- Any `base64:` strings that look like real keys
- Any tokens or authentication strings

### 2. Use Placeholders Only
Always use placeholders in documentation:
```
MAIL_USERNAME=your_username_here
MAIL_PASSWORD=your_password_here
APP_KEY=your_app_key_here
```

### 3. Never Commit These Files
- `.env`
- `.env.production`
- `.env.local`
- Any file with real credentials

### 4. Before Committing Documentation
If creating deployment guides or examples:
- Use `YOUR_VALUE_HERE` placeholders
- Use `your-email@example.com` for emails
- Use `your_api_key` for keys
- Never copy from actual `.env` files

### 5. Double Check Command
Before running `git commit` or `git push`:
```bash
# Check what's being committed
git diff --cached

# Search for potential secrets
git diff --cached | grep -i "password\|key\|secret\|token"
```

## If Secret is Committed:

1. **STOP** - Don't push if not pushed yet
2. **Revoke** the secret immediately
3. **Remove** from git history using `git reset` or `git filter-branch`
4. **Generate** new credentials
5. **Update** all systems with new credentials

## Safe Practices:

✅ Use `.env.example` with placeholders
✅ Add `.env*` to `.gitignore`
✅ Use environment variable names in docs
✅ Review diffs before committing
✅ Use git hooks to scan for secrets

❌ Never commit real credentials
❌ Never copy-paste from `.env` files
❌ Never include real emails/passwords in docs
❌ Never bypass secret scanning warnings
