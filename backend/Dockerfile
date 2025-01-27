FROM php:8.3.16-fpm-alpine3.20

RUN apk add --no-cache git

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions
RUN install-php-extensions opcache
RUN install-php-extensions xdebug

COPY dphp /usr/bin/dphp
RUN chmod +x /usr/bin/dphp

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer --version=2.8.5
ENV COMPOSER_HOME=/.composer
RUN mkdir /.composer && chmod -R 777 /.composer

ARG USER_ID=1000
#RUN addgroup -S www-data
RUN adduser -u $USER_ID -S app -G www-data
USER app

EXPOSE 9000
WORKDIR /app
CMD  ["php-fpm", "-F", "-R", "-O"]
