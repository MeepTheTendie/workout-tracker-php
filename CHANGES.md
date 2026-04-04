# Workout Tracker - Code Improvements Summary

## Changes Made

### 1. Authentication System Improvements

#### Modified Files:
- `includes/auth.php` - Complete rewrite
- `actions/auth/login.php` - Updated to use email + password
- `pages/login.php` - Added email field

#### Changes:
- **Database-based authentication**: Passwords are now verified against the database instead of `.env`
- **Email-based login**: Login now requires email + password (your email: `wesleympennock@gmail.com`)
- **Legacy support**: Maintains backward compatibility with `.env` password during migration
- **Password rehashing**: Automatically rehashes passwords using modern algorithms when needed

### 2. Password Change Feature

#### New Files:
- `pages/change-password.php` - Change password form
- `actions/auth/change-password.php` - Change password handler

#### Modified Files:
- `pages/dashboard.php` - Added link to change password
- `index.php` - Added routes for change-password

#### Features:
- Current password verification
- New password minimum 8 characters
- Password confirmation matching
- Secure password hashing

### 3. DRY Progression Rules

#### New Files:
- `includes/progression.php` - Single source of truth for exercise progression

#### Modified Files:
- `includes/helpers.php` - Removed duplicate progression functions
- `includes/bootstrap.php` - Added progression.php include
- `pages/workouts/log.php` - Updated to use new system

#### Benefits:
- Progression rules defined in ONE place
- PHP and JavaScript both use same data via `getProgressionRulesJson()`
- Easier to add new exercises with progression rules

### 4. Input Validation Layer

#### New Files:
- `includes/validator.php` - Comprehensive validation system

#### Modified Files:
- `includes/bootstrap.php` - Added validator.php include
- `actions/auth/login.php` - Uses validation
- `actions/auth/change-password.php` - Uses validation
- `actions/workouts/add-set.php` - Uses validation
- `actions/workouts/start.php` - Uses validation

#### Features:
- Declarative validation rules: `'field' => 'required|int|min:1|max:999'`
- Type coercion (strings to int/float)
- Automatic error messages
- CSRF validation remains separate

### 5. Race Condition Fix

#### Modified Files:
- `actions/workouts/start.php`

#### Changes:
- Uses database transaction (`dbBegin()` / `dbCommit()` / `dbRollback()`)
- Row-level locking with `FOR UPDATE`
- Prevents duplicate workout creation from double-clicks

### 6. IDOR (Insecure Direct Object Reference) Protection

#### Modified Files:
- `includes/auth.php` - Added `requireOwnership()` function

#### Already Protected:
Most actions already check ownership in their SQL queries (e.g., `WHERE user_id = ?`)

### 7. Testing

#### New Files:
- `e2e/auth.spec.js` - Authentication tests
- `e2e/workout.spec.js` - Workout flow tests
- `e2e/routines.spec.js` - Routine tests
- `e2e/navigation.spec.js` - Navigation and security tests
- `tests/Unit/ValidatorTest.php` - Unit tests for validator
- `tests/Unit/ProgressionTest.php` - Unit tests for progression rules
- `tests/TestHelpers.php` - Test utilities
- `playwright.config.js` - Playwright configuration
- `phpunit.xml` - PHPUnit configuration

#### Modified Files:
- `package.json` - Added test scripts

## Login Credentials

**Email:** `wesleympennock@gmail.com`
**Password:** `GrrMeep#5Dude`

## Testing Commands

```bash
# Run Playwright E2E tests (requires running server)
npm test

# Run PHP Unit tests (requires PHPUnit compatible with your PHP version)
./vendor/bin/phpunit tests/Unit/
```

## Security Improvements

1. ✅ CSRF tokens on all forms
2. ✅ Prepared statements (SQL injection protection)
3. ✅ XSS protection via `htmlspecialchars()`
4. ✅ Password hashing with bcrypt
5. ✅ Session security (httponly, secure, samesite)
6. ✅ Rate limiting
7. ✅ Ownership verification on resources
8. ✅ Input validation layer

## Deployment

Code has been deployed to Hetzner server at:
- **URL:** https://myworkouttracker.xyz
- **Server:** 162.55.208.142
- **Path:** `/var/www/html/`

All changes tested and working:
- ✅ Login with email
- ✅ Password change
- ✅ Start/finish workouts
- ✅ Progression hints
- ✅ CSRF protection
