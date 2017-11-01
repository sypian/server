FROM php:7.1-fpm

COPY . /usr/src/app
WORKDIR /usr/src/app

ENV PATH="/usr/src/app/vendor/bin:${PATH}"

CMD [ "php", "-S", "localhost:8000", "-t", "./public" ]
