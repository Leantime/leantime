FROM php:8.2-cli-alpine

# INSTALL AND UPDATE COMPOSER
COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN composer self-update

WORKDIR /usr/src/app
COPY . .

# INSTALL YOUR DEPENDENCIES
RUN composer install --prefer-dist
