# Deployment Guide

**Last Updated:** March 27, 2026

## Quick Deploy (One-Liner)

```bash
ssh root@162.55.208.142 "cd /var/www/workout-tracker-v2 && git pull origin master"
```

That's it. Done.

---

## Full Deploy Steps

If you need to do it manually:

```bash
# SSH to server
ssh root@162.55.208.142

# Go to app directory
cd /var/www/workout-tracker-v2

# Pull latest changes
git pull origin master

# Check status (should be clean)
git status
```

---

## Server Details

| Item | Value |
|------|-------|
| **Server IP** | 162.55.208.142 |
| **SSH Key** | `~/.ssh/hetzner` |
| **App Directory** | `/var/www/workout-tracker-v2` |
| **Live URL** | https://myworkouttracker.xyz |
| **Git Branch** | `master` |

---

## Git Setup on Production

The production server now has git properly configured:
- Repository cloned from: `https://github.com/MeepTheTendie/workout-tracker.git`
- Active branch: `master`
- `.env` file is preserved (not in git)
- Permissions set: `www-data:www-data`

---

## Troubleshooting

### "Permission denied"
```bash
# Fix permissions
chown -R www-data:www-data /var/www/workout-tracker-v2
chmod 600 /var/www/workout-tracker-v2/.env
```

### "Merge conflicts"
```bash
# Force pull (nuclear option)
cd /var/www/workout-tracker-v2
git fetch origin master
git reset --hard origin/master
```

### ".env file missing"
```bash
# Recreate from example
cd /var/www/workout-tracker-v2
cp .env.example .env
nano .env  # Add real credentials
chmod 600 .env
```

---

## Deploy Checklist

- [ ] Committed all changes locally
- [ ] Pushed to GitHub (`git push origin master`)
- [ ] Ran deploy command
- [ ] Site still loads (https://myworkouttracker.xyz)
- [ ] New feature works

---

**Remember:** Deploy is just `git pull`. Don't overthink it.
