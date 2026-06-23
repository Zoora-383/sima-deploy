FROM php:8.5-fpm-alpine

RUN apk add --no-cache \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    oniguruma-dev \
    unzip \
    git \
    curl \
  && docker-php-ext-install \
    pdo_mysql \
    zip \
    mbstring \
    exif \
    pcntl \
    bcmath \
  && rm -rf /var/cache/apk/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction \
  && mkdir -p /var/www/html/storage/framework/sessions \
            /var/www/html/storage/framework/views \
            /var/www/html/storage/framework/cache \
            /var/www/html/storage/logs \
  && chown -R www-data:www-data /var/www/html \
  && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Allow PHP-FPM workers to inherit container environment variables (e.g. from k8s secrets)
RUN printf "[www]\nclear_env = no\n" > /usr/local/etc/php-fpm.d/zzz-clear-env.conf

RUN apt-get update && apt-get install -y libpng-dev libjpeg-dev libwebp-dev \
        && docker-php-ext-configure gd --with-jpeg --with-webp \
        && docker-php-ext-install gd