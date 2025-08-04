#!/bin/bash
set -e

if echo "$*" | grep -qE "php(cs|md|stan)"; then
    echo "Running safe tool command — skipping Laravel app bootstrap..."
    exec "$@"
fi

if [ ! -f "vendor/autoload.php" ]; then
    composer install --no-progress --no-interaction
fi

if [ ! -f ".env" ]; then
    echo "Creating env file for env $APP_ENV"
    cp .env.example .env
else
    echo "env file exists."
fi

echo "Waiting for PostgreSQL to be ready..."
until pg_isready -h database -p 5432 -U "$DB_USERNAME"; do
  sleep 2
done

php artisan migrate
php artisan key:generate
php artisan cache:clear
php artisan config:clear
php artisan route:clear

exec "$@"