# Workout Tracker v2

A clean, consistent PHP workout tracking application with proper routines support.

## What's New in v2

- **Clean Architecture**: Single pattern (server-side rendering with form POSTs)
- **Working Routines**: Fully functional routine creation, editing, and starting
- **Consistent UI**: Unified design language across all pages
- **Better Security**: Proper CSRF protection on all forms
- **Simpler Codebase**: No mixed AJAX/fetch - pure PHP

## Architecture

```
index.php              # Router - all requests go through here
includes/              # Core functionality
├── bootstrap.php     # App initialization
├── database.php      # Database helpers
├── security.php      # CSRF, validation
├── auth.php          # Authentication
└── helpers.php       # View helpers
pages/                 # Display pages (GET only)
├── dashboard.php
├── login.php
├── routines/         # Routines pages
└── workouts/         # Workout pages
actions/               # Form processors (POST only)
├── auth/
├── routines/         # Routine actions
└── workouts/         # Workout actions
assets/
└── css/style.css     # All styles
```

**Key Principle**: Pages display HTML, Actions process forms and redirect. No mixed AJAX.

## Installation

1. **Clone and configure:**
   ```bash
   git clone <repo> workout-tracker
   cd workout-tracker
   cp .env.example .env
   # Edit .env with your database credentials
   ```

2. **Database:**
   ```bash
   mysql -u root -p < workout-tracker-db.sql
   ```

3. **Web server:**
   - Point document root to project directory
   - Ensure `.htaccess` is enabled (Apache)
   - Or use PHP built-in: `php -S localhost:8080`

4. **Deploy:**
   ```bash
   ./deploy.sh production
   ```

## Configuration (.env)

```bash
DB_HOST=localhost
DB_NAME=workout_tracker
DB_USER=meep
DB_PASS=your_password

APP_ENV=production
APP_URL=https://myworkouttracker.xyz

# Generate password hash:
# php -r "echo password_hash('yourpassword', PASSWORD_BCRYPT);"
ADMIN_PASSWORD_HASH=$2y$12$...
```

## Features

### Workouts
- Start freestyle workout (log as you go)
- Pre-populated sets from routines
- Progression suggestions based on last workout
- Complete/finish with total volume

### Routines
- Create named routines with descriptions
- Add exercises with target sets/reps/weight
- Edit routines anytime
- Start routine → creates workout with pre-populated sets
- Log each set with actual reps/weight

### Stats
- Total workouts, volume, sets
- This week's activity
- Top exercises by volume

## Routes

| Path | Method | Description |
|------|--------|-------------|
| `/` | GET | Redirect to dashboard or login |
| `/login` | GET | Login page |
| `/action/auth/login` | POST | Process login |
| `/dashboard` | GET | Dashboard |
| `/workouts/log` | GET | Active workout logging |
| `/workouts/history` | GET | Past workouts |
| `/workouts/view` | GET | Workout details |
| `/routines` | GET | List routines |
| `/routines/create` | GET | Create routine form |
| `/routines/edit` | GET | Edit routine |
| `/action/*` | POST | Form processors |

## Database Schema

Uses existing `workout_tracker` database with tables:
- `users` - User accounts
- `exercises` - Exercise library
- `workouts` - Workout sessions
- `workout_sets` - Individual sets
- `routines` - Routine templates
- `routine_exercises` - Exercises in routines

## Development

```bash
# Local development
php -S localhost:8080

# Watch for changes (if using browser-sync)
npx browser-sync start --proxy "localhost:8080" --files "**/*.php,**/*.css"
```

## Deployment Checklist

- [ ] Database backed up
- [ ] `.env` configured with production values
- [ ] `.htaccess` enabled
- [ ] File permissions set (644 for files, 755 for dirs, 775 for storage)
- [ ] SSL certificate installed
- [ ] Test login
- [ ] Test routine creation
- [ ] Test workout flow

## Lessons Applied from v1

1. **Single Pattern**: No mixing of AJAX and form POSTs
2. **Clear Separation**: Pages display, Actions process
3. **Proper Redirects**: Actions always redirect, never render
4. **CSRF Everywhere**: All forms include CSRF tokens
5. **Transaction Safety**: Database transactions for multi-step operations
6. **Simple Queries**: Helper functions for common DB operations

## License

MIT
