# syntax=docker/dockerfile:1.7

FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock symfony.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --no-scripts \
    --optimize-autoloader

COPY . ./

ENV APP_ENV=prod
ENV APP_SECRET=build-secret
ENV DATABASE_URL=mysql://user:secret@localhost:3306/database?serverVersion=9.6&charset=utf8mb4
ENV DEFAULT_URI=http://localhost
ENV MEDIA_HOST=http://localhost
ENV CORS_ALLOW_ORIGIN=http://localhost

RUN php bin/console assets:install public --env=prod \
    && composer dump-autoload --classmap-authoritative --no-dev


FROM node:20-bookworm-slim AS assets

WORKDIR /app

COPY package.json package-lock.json ./

RUN npm ci

COPY assets ./assets
COPY webpack.config.js ./
COPY public ./public

RUN npm run build


FROM dunglas/frankenphp:php8.4 AS app

WORKDIR /app

RUN install-php-extensions \
    pdo_mysql \
    gd \
    intl \
    opcache

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

ENV APP_ENV=prod
ENV SERVER_NAME=:80
ENV SERVER_ROOT=/app/public

COPY --from=vendor /app /app
COPY --from=assets /app/public/build /app/public/build
COPY .docker/frankenphp/Caddyfile /etc/caddy/Caddyfile

RUN mkdir -p var/cache var/log var/media public/bundles \
    && chown -R www-data:www-data var public/bundles

VOLUME ["/app/var/media"]
