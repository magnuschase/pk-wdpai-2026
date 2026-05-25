#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(dirname "$SCRIPT_DIR")"
PHAR="$SCRIPT_DIR/phpunit.phar"
PHAR_URL="https://phar.phpunit.de/phpunit-11.phar"

cd "$ROOT_DIR"

if [ ! -f "$PHAR" ]; then
    echo "Downloading PHPUnit 11..."
    curl -sSL "$PHAR_URL" -o "$PHAR"
    chmod +x "$PHAR"
fi

echo "Running unit tests..."
php "$PHAR" --configuration "$SCRIPT_DIR/phpunit.xml" "$@"
