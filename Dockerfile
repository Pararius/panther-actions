ARG PHP_VERSION=8.0
FROM php:${PHP_VERSION}-cli-alpine

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin/
RUN install-php-extensions zip

RUN apk add --no-cache git curl chromium chromium-chromedriver
ENV COMPOSER_MEMORY_LIMIT -1
ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_HOME /composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
