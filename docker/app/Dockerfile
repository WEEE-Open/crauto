FROM php:8-fpm-alpine

RUN apk update \
    && apk add --no-cache --virtual .build-dependencies-in-virtual-world openldap-dev \
    && docker-php-ext-install ldap \
    && docker-php-ext-enable  ldap

RUN mkdir -p /var/www/html
WORKDIR /var/www/html

COPY ./composer.json ./composer.json

RUN php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer

RUN composer install