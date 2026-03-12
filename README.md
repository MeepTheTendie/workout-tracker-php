# Workout Tracker - Laravel Version

This is a complete rewrite of the original PHP workout tracker using the Laravel framework.

## Key Differences from Plain PHP Version

### 1. **Structure**
- **Plain PHP**: All code in root directory, mixed concerns
- **Laravel**: Clear separation (Models, Controllers, Views, Routes)

### 2. **Database**
- **Plain PHP**: Raw SQL queries, no migrations
- **Laravel**: Eloquent ORM, version-controlled migrations, relationships defined in models

### 3. **Authentication**
- **Plain PHP**: Custom session handling, manual password checks
- **Laravel**: Built-in auth system, hashed passwords, middleware protection

### 4. **Routing**
- **Plain PHP**: Manual URL parsing with if/else
- **Laravel**: Clean route definitions, automatic parameter binding

### 5. **Views**
- **Plain PHP**: Raw PHP templates with includes
- **Laravel**: Blade templating engine, layout inheritance

### 6. **Security**
- **Plain PHP**: Manual CSRF tokens, session configuration
- **Laravel**: Automatic CSRF protection, secure defaults

## File Structure

```
app/
  Models/
    Exercise.php          # Exercise model with relationships
    Workout.php           # Workout model with sets relationship
    WorkoutSet.php        # Set model
    User.php              # User with workouts relationship
  Http/Controllers/
    WorkoutController.php # All workout logic

database/
  migrations/             # Version-controlled schema changes
  seeders/               # Test data population

resources/views/
  layouts/app.blade.php  # Main layout template
  auth/login.blade.php   # Login form
  dashboard.blade.php    # Home page
  workouts/              # Workout views

routes/web.php           # All routes defined here
```

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

## Deployment

```bash
./deploy-laravel.sh
```

## Why This Is Better

1. **Migrations**: Database schema is version-controlled. Can rollback if needed.
2. **Seeders**: Can repopulate test data easily.
3. **Relationships**: `$workout->sets` automatically loads related data.
4. **Validation**: Built-in form validation.
5. **Testing**: Can write automated tests.
6. **Maintainability**: Clear structure makes changes easier.
