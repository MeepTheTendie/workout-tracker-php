#!/bin/bash
#
# Deployment Script for Workout Tracker
# Pushes to GitHub and pulls on production server
#

set -e

SERVER="root@162.55.208.142"
SERVER_DIR="/var/www/workout-tracker-v2"
SSH_KEY="~/.ssh/hetzner"

echo "🚀 Deploying workout-tracker..."
echo ""

# Step 1: Check git status
echo "📋 Checking git status..."
if [ -n "$(git status --porcelain)" ]; then
    echo "❌ You have uncommitted changes. Commit first:"
    git status --short
    exit 1
fi
echo "✅ Working tree clean"
echo ""

# Step 2: Show what's being deployed
echo "📝 Last 3 commits:"
git log --oneline -3
echo ""

# Step 3: Pull to ensure we're up to date
echo "🔄 Syncing with remote..."
git pull origin master
echo ""

# Step 4: Push to GitHub
echo "📤 Pushing to GitHub..."
git push origin master
echo ""

# Step 5: Deploy to production
echo "🚀 Deploying to production server..."
ssh -i "$SSH_KEY" "$SERVER" "cd $SERVER_DIR && git pull origin master"
echo ""

# Step 6: Verify
echo "✅ Deployment complete!"
echo ""
echo "🌐 Live site: https://myworkouttracker.xyz"