FROM php:8.2-fpm-alpine

RUN apk add --no-cache bash icu-dev oniguruma-dev zip unzip git \
    && docker-php-ext-install pdo_mysql intl mbstring

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY backend/composer.json backend/composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

COPY backend/ /var/www/html/
RUN chown -R www-data:www-data storage bootstrap/cache

CMD ["php-fpm"]
