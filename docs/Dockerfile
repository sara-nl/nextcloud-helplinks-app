# --- Build stage ------------------------------------------------------------
FROM php:8.2-cli AS builder

# Install build dependencies
RUN apt-get update && apt-get install -y -qq \
    make tar gzip zlib1g-dev libzip-dev curl gnupg \
    zlib1g-dev libzip-dev curl git unzip vim\
    && rm -rf /var/lib/apt/lists/*

# Install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --version=2.9.0 --install-dir=/usr/local/bin --filename=composer
# Run composer inside the app folder
#RUN COMPOSER_ALLOW_SUPERUSER=1 composer install \
#    --no-interaction \
#    --no-progress \
#    --optimize-autoloader
# ---- FIX ENDS HERE ----

# Continue your other steps
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash -
RUN apt-get install -y -qq nodejs
#RUN npm install && npm run build

####
# How to build a new release.
#  1. make a build container
#  docker build -t nextcloud-app-builder .
#
#  2. Build the app 
#  docker run -it -v "$PWD:/app" nextcloud-app-builder /bin/bash
#     root@53dad0ccc8a3:/app# composer install --no-interaction --no-progress --optimize-autoloader
#     root@53dad0ccc8a3:/app# composer update
#     root@53dad0ccc8a3:/app# npm install && npm run build
#     root@53dad0ccc8a3:/app# export RELEASE_VERSION=v1.4.0
#     root@53dad0ccc8a3:/app# version=$RELEASE_VERSION make -e appstore
#
#  3. Create a new release in github and add you tar.gz as attachment
 