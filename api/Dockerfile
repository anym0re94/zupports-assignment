FROM composer:2.5 as build-stage

WORKDIR /app_api

COPY . /app_api

RUN composer install

FROM php:8.1.15-fpm-alpine as deploy-stage

WORKDIR /app_api

COPY ./php/app.ini /usr/local/etc/php/conf.d/app.ini

COPY --from=build-stage /app_api/composer.json /composer.json
COPY --from=build-stage --chown=www-data:www-data /app_api /app_api
