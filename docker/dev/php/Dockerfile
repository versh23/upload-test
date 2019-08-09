FROM php:7.3-fpm-alpine

RUN apk add --no-cache icu-dev zlib-dev libzip-dev \
    && docker-php-ext-install intl pdo_mysql zip \
    && mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini" \
    && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php && mv composer.phar /usr/local/bin/composer

WORKDIR /app