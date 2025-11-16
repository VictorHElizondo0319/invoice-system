FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application directory contents
COPY . /var/www

# Copy existing application directory permissions
COPY --chown=www-data:www-data . /var/www

RUN if [ ! -f .env ]; then cp .env.example .env; fi

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Generate application key if not exists
RUN php artisan key:generate --ansi || true

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]


