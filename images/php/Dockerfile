FROM php:7.1-fpm

ARG sypian_env=development

RUN apt-get update \
    && apt-get install -y git \
    && apt-get clean

COPY install_env_requirements.sh /tmp/docker/install_env_requirements.sh
RUN chmod +x /tmp/docker/install_env_requirements.sh \
    && sh /tmp/docker/install_env_requirements.sh ${sypian_env}

RUN docker-php-ext-install bcmath

ENV PATH="/var/www/html/vendor/bin:${PATH}"

CMD [ "php-fpm" ]
