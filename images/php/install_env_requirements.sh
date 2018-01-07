#!/bin/sh

if [ "$1" = "development" ]
then
    # xdebug
    pecl install xdebug-2.5.4
    docker-php-ext-enable xdebug
fi
