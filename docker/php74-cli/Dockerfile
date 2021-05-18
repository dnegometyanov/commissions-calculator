FROM php:7.4-cli

MAINTAINER Denis Negometyanov <denis.negometyanov@gmail.com>

RUN apt-get update \
    && apt-get install -y curl libfreetype6-dev libmcrypt-dev libzip-dev git \
    && pecl install mcrypt-1.0.3 \
    && docker-php-ext-enable mcrypt \
    && docker-php-ext-install iconv zip opcache \
    && docker-php-ext-install bcmath \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /code

CMD ["tail", "-f", "/dev/null"]