# Migration Guide: v1 → v2

## What Changed

### Architecture
| v1 (Broken) | v2 (Clean) |
|-------------|------------|
| Mixed AJAX + Form POSTs | Server-side only |
| Template + Page hybrid | Unified page system |
| Router with redirects | Router → Pages/Actions |
| Routines disabled | Routines fully working |
| Inconsistent CSRF | CSRF on all forms |

### File Structure
```
v1 (messy)                    v2 (clean)
├── index.php (router)        ├── index.php (clean router)
├── includes/                 ├── includes/
│   ├── config.php (messy)    │   ├── bootstrap.php
│   └── Security.php          │   ├── database.php
├── pages/                    │   ├── security.php
│   └── routines.php (broken) │   ├── auth.php
├── api/                      │   └── helpers.php
│   ├── routine_start.php     ├── pages/
│   └── ... (mixed)           │   ├── dashboard.php
└── templates/ (unused)       │   ├── login.php
                              │   ├── routines/    ← NEW
                              │   └── workouts/
                              ├── actions/         ← NEW
                              │   ├── auth/
                              │   ├── routines/
                              │   └── workouts/
                              └── assets/
                                  └── css/
```

## Database Compatibility

**v2 is fully backward compatible with v1 database.**

No schema changes required. Just point v2 at your existing database:

```bash
# Copy your existing database
mysql -u meep -p workout_tracker_v2 < workout-tracker-db.sql

# Or use existing database directly
# Just update .env with existing credentials
```

## Migration Steps

### 1. Backup Current System
```bash
# Database backup (already done)
mysqldump -u meep -p workout_tracker > backup-v1.sql

# Code backup
cp -r /var/www/workout-tracker /var/backups/workout-tracker-v1
```

### 2. Deploy v2
```bash
# Copy v2 to server
cp -r workout-tracker-v2 /var/www/workout-tracker-new

# Copy .env and configure
cp .env.example .env
nano .env  # Update credentials

# Set permissions
chmod 600 .env
chmod -R 755 storage/
```

### 3. Test Before Switching
```bash
# Test with PHP built-in server
cd /var/www/workout-tracker-new
php -S localhost:8080

# Visit http://localhost:8080
# Test: login → dashboard → routines → start routine → log sets → finish
```

### 4. Switch Over
```bash
# Swap directories
mv /var/www/workout-tracker /var/www/workout-tracker-old
mv /var/www/workout-tracker-new /var/www/workout-tracker

# Or use deploy script
./deploy.sh production
```

### 5. Verify
- [ ] Login works
- [ ] Dashboard shows stats
- [ ] Can create routine
- [ ] Can add exercises to routine
- [ ] Can start routine → creates workout with targets
- [ ] Can log each set with actual reps/weight
- [ ] Can finish workout
- [ ] History shows completed workout

## Key Differences in Usage

### Creating a Routine
**v1**: Broken/disabled
**v2**: 
1. Go to `/routines`
2. Click "+ NEW ROUTINE"
3. Enter name/description
4. Click "CREATE ROUTINE"
5. Add exercises with sets/reps/weight

### Starting a Routine
**v1**: Would fail or redirect
**v2**:
1. Go to `/routines`
2. Click "START" on routine
3. Redirected to `/workouts/log`
4. See pre-populated sets with targets
5. Enter actual reps/weight, click "LOG"
6. Finish workout

### Freestyle Workout (unchanged)
1. Go to `/workouts/log`
2. Click "START NEW WORKOUT"
3. Select exercise, enter reps/weight
4. Click "ADD SET"
5. Finish when done

## Rollback Plan

If something goes wrong:

```bash
# Quick rollback
mv /var/www/workout-tracker /var/www/workout-tracker-v2-failed
mv /var/www/workout-tracker-old /var/www/workout-tracker

# Or restore from backup
cp -r /var/backups/workout-tracker-v1 /var/www/workout-tracker
```

## Troubleshooting

### 404 Errors
- Check `.htaccess` is enabled: `AllowOverride All` in Apache config
- Check `mod_rewrite` is enabled: `a2enmod rewrite && service apache2 restart`

### Database Connection Failed
- Verify `.env` credentials
- Check MySQL is running: `service mysql status`
- Test connection: `mysql -u meep -p -e "SELECT 1"`

### CSRF Errors
- Clear browser cookies/session
- Check `session.cookie_samesite` is set to `Lax` or `Strict`

### Routines Not Showing
- Check database has routines: `SELECT * FROM routines;`
- Check user_id matches: `SELECT * FROM routines WHERE user_id = 1;`

## New Features in v2

1. **Working Routines**: Fully functional create/edit/start
2. **Routine-Based Workouts**: Pre-populated sets with targets
3. **Progressive Overload**: Suggests next weight based on last workout
4. **Better Mobile UI**: Consistent navigation, touch-friendly
5. **Flash Messages**: Success/error feedback on all actions

## Removed in v2

1. **AJAX/fetch calls**: Everything is form POSTs now
2. **Template system**: Unified page system
3. **Mixed API endpoints**: Clean separation of Pages and Actions
4. **Disabled routes**: All routes work now

## Configuration Changes

### v1 config.php
Hardcoded values, messy configuration

### v2 .env
Clean environment variables:
```bash
DB_HOST=localhost
DB_NAME=workout_tracker
DB_USER=meep
DB_PASS=secret
APP_ENV=production
ADMIN_PASSWORD_HASH=$2y$12$...
```

## Support

If migration fails:
1. Check `storage/logs/` for errors
2. Compare your v1 database schema with `workout-tracker-db.sql`
3. Verify all files copied correctly
4. Test with `php -S localhost:8080` locally first
