#!/bin/sh
set -e

echo "Running composer install if needed..."
composer install --no-interaction --prefer-dist --optimize-autoloader


echo "Generating application key if not set..."
if ! grep -q "^APP_KEY=" .env || [ -z "$(grep "^APP_KEY=" .env | cut -d '=' -f2)" ]; then
    php artisan key:generate
fi


echo "Waiting for MySQL to be ready..."
if [ "$DB_HOST" != "" ]; then
    # Wait for the database to be ready
    until nc -z -v -w30 $DB_HOST 3306; do
      echo "Waiting for database connection..."
      # Wait for 5 seconds before check again
      sleep 5
    done

    php artisan migrate --force
fi

# RUN Unit test
php artisan key:generate --env=testing
php artisan test

# Execute the provided command (which is typically php-fpm)
exec "$@"