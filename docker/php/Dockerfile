FROM php:8.2-fpm-alpine

RUN apk add --no-cache bash icu-dev oniguruma-dev zip unzip git \
    && docker-php-ext-install pdo_mysql intl mbstring bcmath opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY backend/composer.json backend/composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts

COPY backend/ /var/www/html/
COPY database/schema_DT07.sql /var/www/html/database-schema.sql
RUN composer dump-autoload --no-dev --optimize \
    && mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && ln -sfn ../storage/app/public public/storage \
    && chown -R www-data:www-data storage bootstrap/cache

CMD ["php-fpm"]
