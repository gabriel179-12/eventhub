FROM php:8.4-fpm-alpine

ARG APP_UID=1000
ARG APP_GID=1000

RUN apk add --no-cache \
        libzip-dev \
        oniguruma-dev \
        postgresql-dev \
    && docker-php-ext-install \
        bcmath \
        mbstring \
        pdo_pgsql \
        zip \
    && addgroup -g ${APP_GID} app \
    && adduser -D -u ${APP_UID} -G app app \
    && sed -ri \
        -e 's!^user = www-data!user = app!' \
        -e 's!^group = www-data!group = app!' \
        /usr/local/etc/php-fpm.d/www.conf

WORKDIR /var/www/api