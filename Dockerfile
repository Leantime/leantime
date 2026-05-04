FROM leantime/leantime:3.7.3

USER root

# Copy forked application code on top of the official image
COPY --chown=www-data:www-data app/          /var/www/html/app/
COPY --chown=www-data:www-data public/       /var/www/html/public/
COPY --chown=www-data:www-data composer.json /var/www/html/composer.json
COPY --chown=www-data:www-data composer.lock /var/www/html/composer.lock

WORKDIR /var/www/html

# Reinstall composer deps to pick up any new packages from the fork
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

USER www-data
