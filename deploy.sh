#!/bin/bash
#
# Deployment Script for Workout Tracker
# Usage: ./deploy.sh [production|staging]
#

set -e

ENVIRONMENT=${1:-production}
TIMESTAMP=$(date +%Y%m%d-%H%M%S)
BACKUP_DIR="/var/backups/workout-tracker"
DEPLOY_DIR="/var/www/workout-tracker"
SOURCE_DIR="$(pwd)"

echo "🚀 Starting deployment to $ENVIRONMENT..."
echo "📅 Timestamp: $TIMESTAMP"

# Create backup directory
mkdir -p "$BACKUP_DIR"

# Backup database before deploy
echo "💾 Backing up database..."
if command -v mysqldump &> /dev/null; then
    mysqldump -u "${DB_USER:-meep}" -p"${DB_PASS}" workout_tracker > \
        "$BACKUP_DIR/workout-tracker-db-$TIMESTAMP.sql" 2>/dev/null || \
        echo "⚠️  Database backup failed (may need credentials)"
else
    echo "⚠️  mysqldump not available, skipping DB backup"
fi

# Backup current deployment (if exists)
if [ -d "$DEPLOY_DIR" ]; then
    echo "📦 Backing up current deployment..."
    cp -r "$DEPLOY_DIR" "$BACKUP_DIR/workout-tracker-code-$TIMESTAMP"
fi

# Create deployment directory
mkdir -p "$DEPLOY_DIR"

# Copy files
echo "📂 Copying files..."
cp -r "$SOURCE_DIR"/* "$DEPLOY_DIR/"

# Copy .env if it doesn't exist
if [ ! -f "$DEPLOY_DIR/.env" ]; then
    echo "⚠️  Creating .env from example - PLEASE UPDATE WITH REAL VALUES!"
    cp "$DEPLOY_DIR/.env.example" "$DEPLOY_DIR/.env"
fi

# Set permissions
echo "🔐 Setting permissions..."
chown -R www-data:www-data "$DEPLOY_DIR"
chmod 755 "$DEPLOY_DIR"
chmod 644 "$DEPLOY_DIR"/*.php
chmod 644 "$DEPLOY_DIR"/*.md
chmod 644 "$DEPLOY_DIR/.env.example"
chmod 600 "$DEPLOY_DIR/.env" 2>/dev/null || true
chmod -R 755 "$DEPLOY_DIR/storage"

# Ensure storage is writable
mkdir -p "$DEPLOY_DIR/storage/logs"
mkdir -p "$DEPLOY_DIR/storage/cache"
chmod -R 775 "$DEPLOY_DIR/storage"

echo "✅ Deployment complete!"
echo ""
echo "Next steps:"
echo "1. Update .env file with production credentials if needed"
echo "2. Test the application at your domain"
echo "3. Check storage/logs/ for any errors"
echo ""
echo "Backup location: $BACKUP_DIR/workout-tracker-code-$TIMESTAMP"
