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
  && chown -R www-data:www-data /var/www/html \
  && chmod -R 755 /var/www/html/storage

# Allow PHP-FPM workers to inherit container environment variables (e.g. from k8s secrets)
RUN printf "[www]\nclear_env = no\n" > /usr/local/etc/php-fpm.d/zzz-clear-env.conf
