FROM php:8.1.1-cli-alpine3.15

RUN apk add --no-cache git

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && install-php-extensions xdebug

ENV XDEBUG_MODE=debug
RUN echo -e "\n\
xdebug.discover_client_host=false\n\
xdebug.start_with_request=trigger\n\
xdebug.trigger_value=PHPSTORM\n\
xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/99-php-xdebug.ini

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer --version=2.2.4
ENV COMPOSER_HOME=/.composer
RUN mkdir /.composer && chmod -R 777 /.composer

WORKDIR /app
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public/"]