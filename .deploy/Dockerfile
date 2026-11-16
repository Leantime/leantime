# Build stage
FROM serversideup/php:8.4.27-cli AS builder

# Switch to root user first
USER root
RUN install-php-extensions pcntl opcache pdo pdo_mysql mysqli intl zip gd exif ftp bcmath ldap mbstring redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Node.js using multi-stage approach (same major as the release build on GitHub runners)
COPY --from=node:20 /usr/local/bin/node /usr/local/bin/node
COPY --from=node:20 /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm && \
    ln -s /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx

# Install build dependencies
RUN apt update && apt install -y \
    bash make curl unzip git libicu-dev libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libssl-dev libldap2-dev openssl \
    libonig-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Set working directory
WORKDIR /app

# Copy composer files first for better caching
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader
#
## Copy package.json and build JS assets
COPY package.json package-lock.json ./
RUN npm install

# Copy the rest of the application code
COPY . .

# Complete Composer installation
RUN composer dump-autoload --no-dev --optimize

# Run additional build steps (like Laravel Mix)
RUN npx mix --production
RUN node generateBlocklist.mjs

# Production stage
# NGINX Unit is discontinued and serversideup stopped publishing the -unit images
# (frozen at PHP 8.3.27, 2025-11). fpm-nginx is their maintained variant.
# Pinned to the patch release so a Coolify cache never serves a stale PHP build;
# bump both FROM lines together.
FROM serversideup/php:8.4.27-fpm-nginx AS production

# Environment variables for configuration
ENV SSL_MODE=off
ENV PHP_OPCACHE_ENABLE=1
ENV PHP_MEMORY_LIMIT=256M
ENV PHP_MAX_EXECUTION_TIME=120
ENV PHP_MAX_INPUT_TIME=120
ENV PHP_UPLOAD_MAX_FILE_SIZE=64M
ENV PHP_POST_MAX_SIZE=64M
ENV NGINX_WEBROOT=/var/www/html/public
ENV TRUSTED_PROXY=cloudflare

# Same worker budget as the old Unit config (max 10, 2 spare, recycle after 1000 requests)
ENV PHP_FPM_PM_CONTROL=dynamic
ENV PHP_FPM_PM_MAX_CHILDREN=10
ENV PHP_FPM_PM_START_SERVERS=2
ENV PHP_FPM_PM_MIN_SPARE_SERVERS=2
ENV PHP_FPM_PM_MAX_SPARE_SERVERS=4
ENV PHP_FPM_PM_MAX_REQUESTS=1000

# Install runtime extensions
USER root
RUN install-php-extensions pcntl opcache pdo pdo_mysql mysqli intl zip gd exif ftp bcmath ldap mbstring redis \
    && apt-get update \
    && apt-get install -y bash vim mariadb-client \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /var/www/html

# Copy only the files needed for production from builder
COPY --from=builder --chown=www-data:www-data /app/app ./app
COPY --from=builder --chown=www-data:www-data /app/bin ./bin
COPY --from=builder --chown=www-data:www-data /app/bootstrap ./bootstrap
COPY --from=builder --chown=www-data:www-data /app/public ./public
COPY --from=builder --chown=www-data:www-data /app/vendor ./vendor
COPY --from=builder --chown=www-data:www-data /app/storage ./storage

# Create .deploy directory if it doesn't exist and copy config files
RUN mkdir -p .deploy/entrypoint.d

# Copy any startup scripts (if they exist)
COPY --chmod=755 .deploy/entrypoint.d/ /etc/entrypoint.d/

# Keep nginx's dotfile/backup-file blocking but drop its security headers:
# Leantime's InitialHeaders middleware owns those (and plugins filter them).
COPY .deploy/nginx/security.conf /etc/nginx/server-opts.d/security.conf

# Ensure proper permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Switch to non-root user
USER www-data

# nginx listens on 8080 (SSL_MODE=off)
EXPOSE 8080
