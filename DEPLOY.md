# Deployment Guide

## Quick Deploy

```bash
./deploy.sh
```

That's it. The script handles everything.

---

## Manual Deploy

If you prefer to do it step by step:

```bash
# 1. Commit and push changes
git add .
git commit -m "Your message"
git push origin master

# 2. Pull on server
ssh root@162.55.208.142 "cd /var/www/workout-tracker-v2 && git pull origin master"
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

## Troubleshooting

### "Permission denied"
```bash
ssh -i ~/.ssh/hetzner root@162.55.208.142
chown -R www-data:www-data /var/www/workout-tracker-v2
chmod 600 /var/www/workout-tracker-v2/.env
```

### "Merge conflicts" on server
```bash
ssh -i ~/.ssh/hetzner root@162.55.208.142
cd /var/www/workout-tracker-v2
git fetch origin
git reset --hard origin/master
```

### ".env file missing" on server
```bash
ssh -i ~/.ssh/hetzner root@162.55.208.142
cd /var/www/workout-tracker-v2
cp .env.example .env
nano .env  # Add real credentials
chmod 600 .env
```

### Uncommitted changes locally
```bash
git status        # See what changed
git diff          # Review changes
git add .         # Stage all
git commit -m " " # Commit
git push          # Push
./deploy.sh       # Deploy
```

---

## Deploy Checklist

- [ ] Changes committed locally
- [ ] `./deploy.sh` ran successfully
- [ ] Site loads: https://myworkouttracker.xyz
- [ ] New feature works