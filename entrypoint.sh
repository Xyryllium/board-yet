#!/bin/bash
set -e

TOOL_COMMANDS=("phpstan" "phpmd" "phpcs" "artisan test" "artisan tinker")
for TOOL in "${TOOL_COMMANDS[@]}"; do
  if echo "$*" | grep -q "$TOOL"; then
    echo "Running tool: $TOOL — skipping Laravel app bootstrap..."
    exec "$@"
  fi
done

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