FROM php:7.4-fpm

MAINTAINER jeyroik <jeyroik@gmail.com>

RUN apt-get update && apt-get install -y git zip unzip \
    && apt-get install -y libcurl4-openssl-dev pkg-config libssl-dev \
    && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && rm composer-setup.php \
    && docker-php-ext-install opcache \
    && pecl install mongodb apcu && docker-php-ext-enable mongodb apcu opcache

COPY resources/docker.env.dist .env
COPY src src
COPY composer.json composer.json
COPY extas.json extas.json
COPY resources resources

RUN composer u && ln -S index.php vendor/jeyroik/extas-api/public/index.php