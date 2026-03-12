#!/bin/bash
# Deployment script with staging test

set -e

SERVER="root@164.92.109.42"
STAGING_URL="http://staging.myworkouttracker.xyz"
PROD_URL="https://myworkouttracker.xyz"

echo "=== Workout Tracker Deployment ==="
echo ""

# Check for uncommitted changes
if [ -n "$(git status --porcelain 2>/dev/null)" ]; then
    echo "⚠️  WARNING: You have uncommitted changes!"
    echo "Commit first: git add -A && git commit -m 'your message'"
    read -p "Continue anyway? (y/N) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

echo "Step 1: Deploying to STAGING..."
rsync -avz --exclude='.git' --exclude='data' --exclude='vendor' ./ $SERVER:/var/www/staging-workout/

echo ""
echo "Step 2: Testing STAGING..."
# Test login
if curl -s -X POST "$STAGING_URL/api/auth.php?action=login" \
    -H "Content-Type: application/json" \
    -d '{"password":"testpassword"}' | grep -q "success"; then
    echo "✅ Staging login works"
else
    echo "❌ Staging login FAILED"
    exit 1
fi

# Test workout page
if curl -s -b /tmp/staging_cookie.txt "$STAGING_URL/?page=workout" | grep -q "startWorkoutBtn"; then
    echo "✅ Staging workout page loads"
else
    echo "❌ Staging workout page FAILED"
    exit 1
fi

echo ""
read -p "Staging looks good. Deploy to PRODUCTION? (y/N) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Cancelled. Staging is at $STAGING_URL"
    exit 0
fi

echo ""
echo "Step 3: Deploying to PRODUCTION..."
rsync -avz --exclude='.git' --exclude='data' --exclude='vendor' ./ $SERVER:/var/www/workout-tracker-php/

echo ""
echo "Step 4: Testing PRODUCTION..."
if curl -s -X POST "$PROD_URL/api/auth.php?action=login" \
    -H "Content-Type: application/json" \
    -d '{"password":"GrrMeep#5Dude"}' | grep -q "success"; then
    echo "✅ Production login works"
else
    echo "❌ Production login FAILED - CHECK IMMEDIATELY"
    exit 1
fi

echo ""
echo "=== DEPLOYMENT COMPLETE ==="
echo "Production: $PROD_URL"
echo "Staging: $STAGING_URL (for next time)"
