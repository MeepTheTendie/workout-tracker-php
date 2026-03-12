#!/bin/bash
# Local dev environment setup for workout-tracker

set -e

echo "=== Setting up local dev environment ==="

# Check if we're in the right directory
if [ ! -f "config.php" ]; then
    echo "Error: Run this from workout-tracker-php directory"
    exit 1
fi

# Create local .env if it doesn't exist
if [ ! -f ".env" ]; then
    echo "Creating local .env..."
    printf 'APP_PASSWORD=devpassword' > .env
    echo "Created .env with dev password"
fi

# Create data directory
mkdir -p data

# Remove DATABASE_URL to force SQLite (local dev)
unset DATABASE_URL

# Start local server
echo "Starting dev server on http://localhost:8080"
echo "Password: devpassword"
echo ""
php -S localhost:8080 -t .
