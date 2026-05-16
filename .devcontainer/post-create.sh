#!/usr/bin/env bash
# Runs once after the dev container is created.
# Idempotent: safe to re-run.

set -euo pipefail

cd /var/www/html

echo "→ Composer install"
if [ -f composer.json ]; then
    composer install --no-interaction --prefer-dist --no-progress
fi

echo "→ NPM install"
if [ -f package.json ]; then
    npm ci --no-audit --no-fund || npm install --no-audit --no-fund
fi

echo "→ .env"
if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
fi

echo "→ APP_KEY"
if [ -f .env ] && ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --ansi || true
fi

echo "→ Storage symlink"
php artisan storage:link --ansi || true

echo "→ Migrate"
php artisan migrate --no-interaction --force || true

echo "→ Frontend build (one-shot; run 'npm run dev' for live reload)"
npm run build || true

echo "✅ Dev container ready."
echo "Useful commands:"
echo "  vendor/bin/sail artisan test --compact"
echo "  vendor/bin/sail bin pint --dirty --format agent"
echo "  vendor/bin/sail npm run dev"
