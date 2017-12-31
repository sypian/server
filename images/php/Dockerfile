FROM php:7.1-fpm

COPY . /usr/src/app
WORKDIR /usr/src/app

RUN apt-get update \
    && apt-get install -y git \
    && apt-get clean

RUN pecl install xdebug-2.5.4 \
    && docker-php-ext-enable xdebug

RUN docker-php-ext-install bcmath

ENV PATH="/usr/src/app/vendor/bin:${PATH}"

CMD [ "php", "-S", "localhost:8000", "-t", "./public" ]
