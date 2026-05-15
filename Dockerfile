FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo_pgsql pgsql \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html/PWD-Application-System

COPY . /var/www/html/PWD-Application-System
COPY php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

RUN sed -ri 's!DocumentRoot /var/www/html!DocumentRoot /var/www/html/PWD-Application-System!g' /etc/apache2/sites-available/000-default.conf \
    && printf '%s\n' '<Directory /var/www/html/PWD-Application-System>' '    AllowOverride All' '</Directory>' >> /etc/apache2/apache2.conf

EXPOSE 80
