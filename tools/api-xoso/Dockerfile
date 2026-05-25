FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader

FROM php:8.3-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
  icu-dev \
  libzip-dev \
  oniguruma-dev \
  zlib-dev \
  && docker-php-ext-install -j$(nproc) \
  intl \
  mbstring \
  pdo \
  pdo_mysql \
  zip \
  bcmath \
  opcache \
  && apk del --no-cache icu-dev libzip-dev oniguruma-dev zlib-dev

COPY --from=vendor /app/vendor ./vendor
COPY . .

RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

