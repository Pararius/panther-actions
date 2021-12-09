# base php image
FROM php:8.0-alpine AS php

RUN apk add --no-cache git curl chromium chromium-chromedriver
ENV COMPOSER_MEMORY_LIMIT -1
ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_HOME /composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
