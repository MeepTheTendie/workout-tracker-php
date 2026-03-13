#!/bin/bash
echo "Linting PHP files..."
ERRORS=$(find . -name '*.php' -not -path './vendor/*' -not -path './storage/*' -exec php -l {} \; 2>&1 | grep -E '^\s*\./.*error|Error.*in' || true)

if [ -n "$ERRORS" ]; then
    echo "$ERRORS"
    echo "✗ Syntax errors found!"
    exit 1
fi

echo "✓ All PHP files have valid syntax!"
exit 0
