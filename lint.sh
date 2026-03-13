#!/bin/bash
echo "Linting PHP files..."
find . -name '*.php' -not -path './vendor/*' -not -path './storage/*' -exec php -l {} \; 2>&1 | grep -E 'error|Error'
if [ $? -eq 1 ]; then
    echo "✓ All PHP files have valid syntax!"
    exit 0
else
    echo "✗ Syntax errors found!"
    exit 1
fi
