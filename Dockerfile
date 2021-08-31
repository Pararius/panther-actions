# base php image
FROM php:8.0.10-fpm-alpine3.13 AS php
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin/

RUN apk add --no-cache git curl chromium chromium-chromedriver
ENV COMPOSER_MEMORY_LIMIT -1
ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_HOME /composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN install-php-extensions \
      intl \
      opcache \
      pcntl \
      zip \
    ;
RUN mv $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini
WORKDIR /app
