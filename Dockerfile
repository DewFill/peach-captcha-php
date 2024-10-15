FROM php:8.3-apache

RUN a2enmod rewrite

RUN docker-php-ext-install pdo pdo_mysql

# This version requires Internet connection to download Composer Installer.
# If you wish not to use Internet connection then you have to manually download composer.phar executable
# (Don't forget to remove installation in Dockerfile and change start command in Makefile)
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer


RUN composer --version