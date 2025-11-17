FROM php:8.2-fpm

# System deps
RUN apt-get update && apt-get install -y \
    git curl unzip libpng-dev libonig-dev libxml2-dev libpq-dev libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# Use Laravel project root
WORKDIR /var/www

# 1) copy only composer files (cache-friendly)
COPY composer.json composer.lock ./

# 2) install deps WITHOUT scripts (artisan not present yet)
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# 3) now copy the rest of the app (artisan becomes available)
COPY . .

# 4) run full install (this time scripts can run)
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# 5) make sure .env exists, then generate key
RUN if [ ! -f .env ]; then cp .env.example .env; fi \
 && php artisan key:generate --force

# permissions (optional but helpful)
RUN chown -R www-data:www-data /var/www

EXPOSE 8000
CMD ["php-fpm"]
