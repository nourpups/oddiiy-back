FROM php:8.2-fpm-alpine

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN apk add --no-cache \
      libpq-dev \
      libpng-dev \
      libzip-dev \
      zip \
      unzip \
      git \
      && install-php-extensions \
        pdo_mysql \
        bcmath \
        gd \
        zip \
        intl

ENV COMPOSER_ALLOW_SUPERUSER=1

COPY --from=composer:2.7.7 /usr/bin/composer /usr/bin/composer

# нужен чтобы работал tinker
# https://stackoverflow.com/questions/46467774/laravel-writing-to-config-psysh-is-not-allowed#comment130111227_73635736

WORKDIR /var/www/oddiiy-back

COPY ./composer.* .

RUN composer install --prefer-dist --ignore-platform-reqs --no-progress --no-scripts --no-interaction

COPY ./ ./

RUN adduser -D -u 1000 -s /bin/bash xuy www-data \
    && chown -R xuy:xuy ./
