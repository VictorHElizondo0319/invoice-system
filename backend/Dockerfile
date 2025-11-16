FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl unzip libpng-dev libonig-dev libxml2-dev libpq-dev libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy composer files first (for caching)
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy the rest of the application files
COPY . .

# Ensure proper permissions
RUN chown -R www-data:www-data /var/www

# ✅ Ensure .env exists before artisan commands
RUN if [ ! -f .env ]; then cp .env.example .env; fi

# ✅ Generate app key safely
RUN php artisan key:generate --force || true

EXPOSE 9000
CMD ["php-fpm"]
