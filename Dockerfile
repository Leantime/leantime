FROM leantime/leantime:3.7.3

USER root

# Copy forked app code on top of the official image.
# The base image already has all vendor dependencies installed;
# no new composer packages were added in this fork.
COPY --chown=www-data:www-data app/    /var/www/html/app/
COPY --chown=www-data:www-data public/ /var/www/html/public/

USER www-data
