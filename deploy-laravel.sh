#!/bin/bash
# Laravel deployment script

SERVER="root@164.92.109.42"
APP_DIR="/var/www/workout-tracker-laravel"

echo "=== Deploying Laravel Workout Tracker ==="

# Install dependencies locally first
echo "Installing dependencies..."
composer install --no-dev --optimize-autoloader

# Create .env for production
cat > .env.production << EOF
APP_NAME="Workout Tracker"
APP_ENV=production
APP_KEY=base64:$(php -r "echo base64_encode(random_bytes(32));")
APP_DEBUG=false
APP_URL=https://laravel.myworkouttracker.xyz

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=workout_tracker_laravel
DB_USERNAME=postgres
DB_PASSWORD=workout123

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
EOF

# Create deployment archive
echo "Creating deployment archive..."
tar -czf /tmp/laravel-deploy.tar.gz \
    --exclude='.git' \
    --exclude='node_modules' \
    --exclude='tests' \
    --exclude='.env' \
    --exclude='storage/logs/*' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    .

# Deploy to server
echo "Deploying to server..."
ssh $SERVER "mkdir -p $APP_DIR"
scp /tmp/laravel-deploy.tar.gz $SERVER:/tmp/
ssh $SERVER "
    cd $APP_DIR
    tar -xzf /tmp/laravel-deploy.tar.gz
    cp .env.production .env
    
    # Create storage directories and set permissions
    mkdir -p storage/framework/{cache,sessions,views}
    mkdir -p storage/logs
    chown -R www-data:www-data storage
    chmod -R 775 storage
    chown -R www-data:www-data bootstrap/cache
    chmod -R 775 bootstrap/cache
    
    # Create database
    sudo -u postgres psql -c 'CREATE DATABASE workout_tracker_laravel;' 2>/dev/null || echo 'DB exists'
    
    # Run migrations
    php artisan migrate --force
    php artisan db:seed --force
    
    # Optimize
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
"

echo "=== Deployment Complete ==="
echo "Next step: Configure Apache/nginx to point to $APP_DIR/public"
