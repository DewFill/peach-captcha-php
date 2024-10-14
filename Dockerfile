FROM php:8.3-apache

RUN a2enmod rewrite

RUN docker-php-ext-install pdo pdo_mysql

RUN apt-get update && apt-get install -y \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer --version