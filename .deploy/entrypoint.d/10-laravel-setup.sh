#!/bin/bash
set -e

echo "Setting up Leantime..."
cd /var/www/html

# Run composer if needed
#if [ ! -d "vendor" ]; then
#  composer install --no-interaction --no-dev --optimize-autoloader
#fi

# Laravel cache and optimize commands
# php artisan config:cache
# php artisan route:cache
# php artisan view:cache

# Run migrations if needed
# php artisan migrate --force

# Warm-up steps are best-effort: this script runs with `set -e`, so an unguarded
# failing command kills the container and takes production down in a restart loop
# (2026-07-05 outage: an entrypoint call to a command the build didn't ship).
php /var/www/html/bin/leantime package:discover || echo "WARN: package:discover failed (non-fatal)"
php /var/www/html/bin/leantime cache:clearAll || echo "WARN: cache:clearAll failed (non-fatal)"
php /var/www/html/bin/leantime language:clear || echo "WARN: language:clear failed (non-fatal)"

echo "Leantime setup complete!"
