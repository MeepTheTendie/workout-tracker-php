# Workout Tracker

A clean PHP workout tracking application with routines, cardio, stats, and body composition tracking.

## Architecture

```
index.php              # Router - all requests go through here
includes/              # Core functionality
├── bootstrap.php     # App initialization
├── database.php      # Database helpers
├── database-sqlite.php  # SQLite drop-in for local testing
├── security.php      # CSRF, validation
├── auth.php          # Authentication
├── helpers.php       # View helpers
├── analytics.php     # Stats calculation
└── error-handler.php # Error handling
pages/                 # Display pages (GET only)
├── dashboard.php
├── login.php
├── stats.php
├── goals.php
├── prs.php
├── cardio.php
├── export.php
├── routines/         # Routines pages
└── workouts/         # Workout pages
actions/               # Form processors (POST only)
├── auth/
├── routines/         # Routine actions
├── workouts/         # Workout actions
└── cardio/           # Cardio actions
assets/
└── css/style.css     # All styles
storage/
└── workout_tracker.db  # SQLite file for local testing
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
   ./deploy.sh
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
- Edit or remove sets during a workout

### Routines
- Create named routines with descriptions
- Add exercises with target sets/reps/weight
- Edit routines anytime
- Start routine → creates workout with pre-populated sets
- Log each set with actual reps/weight
- Update exercise targets in existing routines

### Stats
- Total workouts, volume, sets
- This week's activity
- Top exercises by volume

### Cardio
- Log cardio sessions (bike, treadmill, etc.)
- Track duration, calories, heart rate

### PRs & Goals
- Track personal records by exercise
- Set and monitor fitness goals

### Export
- Export workout history for backup or analysis

## Routes

| Path | Method | Description |
|------|--------|-------------|
| `/` | GET | Redirect to dashboard or login |
| `/login` | GET | Login page |
| `/action/auth/login` | POST | Process login |
| `/action/auth/logout` | POST | Logout |
| `/dashboard` | GET | Dashboard |
| `/workouts/log` | GET | Active workout logging |
| `/workouts/history` | GET | Past workouts |
| `/workouts/view` | GET | Workout details |
| `/action/workouts/start` | POST | Start freestyle workout |
| `/action/workouts/add-set` | POST | Add a set |
| `/action/workouts/complete-set` | POST | Complete routine-based set |
| `/action/workouts/edit-set` | POST | Edit a set |
| `/action/workouts/finish` | POST | Finish workout |
| `/action/workouts/update-name` | POST | Rename workout |
| `/routines` | GET | List routines |
| `/routines/create` | GET | Create routine form |
| `/routines/edit` | GET | Edit routine |
| `/action/routines/create` | POST | Create routine |
| `/action/routines/update` | POST | Update routine name/desc |
| `/action/routines/delete` | POST | Delete routine |
| `/action/routines/start` | POST | Start routine → create workout |
| `/action/routines/add-exercise` | POST | Add exercise to routine |
| `/action/routines/remove-exercise` | POST | Remove exercise from routine |
| `/action/routines/update-exercise` | POST | Update exercise target |
| `/stats` | GET | Statistics |
| `/goals` | GET | Goals |
| `/prs` | GET | Personal records |
| `/cardio` | GET | Cardio log |
| `/action/cardio/add` | POST | Add cardio session |
| `/export` | GET | Export data |

## Database Schema

Tables:
- `users` - User accounts
- `exercises` - Exercise library
- `workouts` - Workout sessions
- `workout_sets` - Individual sets
- `routines` - Routine templates
- `routine_exercises` - Exercises in routines
- `cardio_sessions` - Cardio tracking
- `body_composition_scans` - InBody / body comp data
- `goals` - Fitness goals

## Development

```bash
# Local development (uses SQLite automatically if MySQL is unavailable)
php -S localhost:8080

# Run tests
./vendor/bin/phpunit
```

## Deployment Checklist

- [ ] Database backed up
- [ ] `.env` configured with production values
- [ ] `.htaccess` enabled
- [ ] File permissions set (644 for files, 755 for dirs, 600 for .env)
- [ ] SSL certificate installed
- [ ] Test login
- [ ] Test routine creation
- [ ] Test workout flow

## License

MIT
