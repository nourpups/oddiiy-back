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
        intl \
        exif

ENV COMPOSER_ALLOW_SUPERUSER=1

COPY --from=composer:2.7.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/oddiiy-back

COPY ./composer.* .

RUN composer install --prefer-dist --ignore-platform-reqs --no-progress --no-scripts --no-interaction

COPY ./ ./

# нужен чтобы работали добавленные алиасы (https://serverfault.com/a/1109046)
# закоменчен по причине 47 строка
#ENV ENV="/etc/profile"

# создаем executable чтобы сократить сэкономить время, пример:
# docker exec -it <app_cntnr_name> php artisan ... --> docker exec -it <app_cntnr_name> a ...
# (создание простых alias не панацея, бикос оф https://superuser.com/a/1209426)
RUN echo '#! /bin/sh' >> /usr/local/bin/a \
    && echo 'php artisan "$@"' >> /usr/local/bin/a \
    && chmod 755 /usr/local/bin/a

# нужен чтобы работал tinker
# https://stackoverflow.com/questions/46467774/laravel-writing-to-config-psysh-is-not-allowed#comment130111227_73635736
RUN adduser -D -u 1000 -s /bin/bash xuy www-data \
    && chown -R xuy:xuy ./
#    && echo 'alias a="php artisan"' >> /etc/profile.d/custom_aliases.sh
# добавления алиаса, не нужен из-за executable'а сверху, не удаляю на будущее может понадобиться
