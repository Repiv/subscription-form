FROM php:7.3-fpm

RUN pecl install apcu

RUN apt-get update && apt-get install -y libzip-dev

RUN docker-php-ext-install zip
RUN docker-php-ext-enable apcu

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && chmod +x /usr/local/bin/composer

WORKDIR /usr/src/app

COPY app/symfony-app /usr/src/app

RUN mkdir /usr/src/app/data && chmod 777 /usr/src/app/data

RUN composer install

RUN PATH=$PATH:/usr/src/apps/vendor/bin:bin