#!/bin/sh
set -e

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

# Execute the provided command (which is typically php-fpm)
exec "$@"