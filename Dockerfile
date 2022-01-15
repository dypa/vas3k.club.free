FROM php:8.1.1-cli-alpine3.15

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer --version=2.2.4
ENV COMPOSER_HOME=/.composer
RUN mkdir /.composer && chmod -R 777 /.composer

WORKDIR /app

CMD ["php", "/app/app.php"]