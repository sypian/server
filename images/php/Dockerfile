FROM php:7.1-fpm

ARG sypian_env=development
COPY . /usr/src/app
WORKDIR /usr/src/app

RUN apt-get update \
    && apt-get install -y git \
    && apt-get clean

COPY install_env_requirements.sh /tmp/docker/install_env_requirements.sh
RUN chmod +x /tmp/docker/install_env_requirements.sh \
    && sh /tmp/docker/install_env_requirements.sh ${sypian_env}

RUN docker-php-ext-install bcmath

ENV PATH="/usr/src/app/vendor/bin:${PATH}"

CMD [ "php", "-S", "localhost:8000", "-t", "./public" ]
