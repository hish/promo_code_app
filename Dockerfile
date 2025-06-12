FROM php:8.3.21-cli

# Set working directory inside the container
WORKDIR /app

# Install system dependencies and PHP extensions needed for Laravel
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libxml2-dev \
    zip \
    curl \
    netcat-traditional \
    libonig-dev \
    pkg-config \
    libssl-dev \
    build-essential \
    && docker-php-ext-install pdo_mysql zip xml mbstring 

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy the entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Copy the rest of the application files
COPY . /app

# Install PHP dependencies via Composer
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Expose port 8000 for Laravel development server
EXPOSE 8000

ENTRYPOINT ["docker-entrypoint.sh"]

# Start Laravel development server on container start
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
