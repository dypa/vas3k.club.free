FROM php:8.3.4-fpm-alpine3.19

RUN apk add --no-cache git

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && install-php-extensions xdebug

COPY dphp /usr/bin/dphp
RUN chmod +x /usr/bin/dphp

ENV XDEBUG_MODE=debug
ENV PHP_IDE_CONFIG="serverName=vas3k-club-reader"   
RUN echo -e "\n\
    xdebug.discover_client_host=false\n\
    xdebug.start_with_request=trigger\n\
    xdebug.idekey=PHPSTORM\n\
    xdebug.client_host=host.docker.internal\n\
    xdebug.client_port=9003\n" >> /usr/local/etc/php/conf.d/99-php-xdebug.ini

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer --version=2.7.2
ENV COMPOSER_HOME=/.composer
RUN mkdir /.composer && chmod -R 777 /.composer

EXPOSE 9000
WORKDIR /app
CMD  ["php-fpm", "-F", "-R", "-O"]