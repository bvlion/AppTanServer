FROM php:8.2-apache-bullseye

RUN apt-get update && apt-get install -y git zip unzip libzip-dev curl gnupg libmecab-dev mecab mecab-ipadic-utf8


COPY php-base.ini $PHP_INI_DIR/conf.d/

COPY httpd-base.conf /etc/apache2/sites-available/slim.conf
RUN a2dissite 000-default.conf && a2ensite slim.conf && a2enmod rewrite

RUN pecl install xdebug && docker-php-ext-enable xdebug

RUN apt-get install -y zlib1g-dev libpq-dev libicu-dev libfreetype6-dev libjpeg62-turbo-dev libpng-dev

RUN docker-php-ext-install pdo_mysql intl gd zip && docker-php-ext-configure gd