FROM php:8.2-fpm-alpine
RUN apk add --no-cache nginx supervisor icu-dev oniguruma-dev zip unzip git \
    && docker-php-ext-install pdo_mysql intl mbstring bcmath opcache
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY backend/composer.json backend/composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts
COPY backend/ ./
COPY database/schema_DT07.sql ./database-schema.sql
RUN composer dump-autoload --no-dev --optimize \
    && mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache /run/nginx \
    && chown -R www-data:www-data storage bootstrap/cache
COPY docker/render/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/render/php-fpm.conf /usr/local/etc/php-fpm.d/zz-render.conf
COPY docker/render/supervisord.conf /etc/supervisord.conf
COPY docker/render/start.sh /usr/local/bin/dt07-start
RUN chmod +x /usr/local/bin/dt07-start
EXPOSE 8080
CMD ["dt07-start"]
