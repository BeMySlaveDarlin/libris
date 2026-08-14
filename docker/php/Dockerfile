FROM php:8.4-fpm-alpine

RUN apk add --no-cache git unzip libzip-dev icu-dev freetype-dev libjpeg-turbo-dev libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql zip intl gd opcache bcmath \
    && apk del --no-network git \
    && rm -rf /var/cache/apk/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY php.ini /usr/local/etc/php/conf.d/app.ini
COPY www.conf /usr/local/etc/php-fpm.d/zz-app.conf

WORKDIR /app
