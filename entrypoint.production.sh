#!/bin/bash
set -e

echo "Starting production deployment process..."

if echo "$*" | grep -qE "php(cs|md|stan)"; then
    echo "Running safe tool command — skipping Laravel app bootstrap..."
    exec "$@"
fi

if [ "$APP_ENV" = "production" ]; then
    echo "Setting up production environment..."
    
    if [ ! -f "vendor/autoload.php" ]; then
        echo "Installing Composer dependencies..."
        composer install --no-dev --optimize-autoloader --no-interaction
    fi

    if [ ! -f ".env" ]; then
        echo "ERROR: .env file not found in production!"
        echo "Please ensure you have copied your production .env file to the container."
        exit 1
    fi

    echo "Optimizing Laravel for production..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache

    chown -R www-data:www-data /var/www/storage
    chown -R www-data:www-data /var/www/bootstrap/cache
    chmod -R 755 /var/www/storage
    chmod -R 755 /var/www/bootstrap/cache

    echo "Production setup completed successfully!"
else
    echo "Non-production environment detected, skipping production optimizations..."
fi

exec "$@"
