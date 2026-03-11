#!/bin/bash
# Load environment variables from .env if it exists
if [ -f .env ]; then
    export $(grep -v '^#' .env | xargs)
fi
php -S localhost:8080 -t .
