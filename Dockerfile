FROM node:20-alpine AS frontend-builder

WORKDIR /build

COPY package*.json ./
COPY webpack.mix.js ./
COPY tailwind.config.js ./

RUN npm ci --production=false

# Copy source files needed for build
COPY public ./public
COPY resources ./resources

# Create minimal .babelrc to satisfy ESLint requirement
RUN echo '{"presets": ["@babel/preset-env"]}' > .babelrc

# Build frontend assets
RUN npx mix --production

# Verify build outputs and create mix-manifest.json if it doesn't exist
RUN echo "=== Checking build outputs ===" && \
    ls -la public/dist/ || echo "Warning: public/dist not found" && \
    ls -la public/ | grep mix-manifest || echo "Warning: public/mix-manifest.json not found" && \
    ls -la . | grep mix-manifest || echo "Warning: mix-manifest.json not found in root" && \
    if [ ! -f mix-manifest.json ] && [ ! -f public/mix-manifest.json ]; then \
        echo "Creating empty mix-manifest.json"; \
        echo '{}' > mix-manifest.json; \
    fi && \
    echo "=== Build outputs check complete ==="

# Generate blocklist
COPY generateBlocklist.mjs ./
RUN node generateBlocklist.mjs

# Stage 2: Install PHP dependencies
FROM php:8.2-fpm-alpine AS composer-builder

WORKDIR /build

# Install composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install system dependencies (keep runtime libs, only remove build deps)
RUN apk add --no-cache \
    # Runtime dependencies (KEEP THESE)
    freetype \
    libjpeg-turbo \
    libpng \
    libwebp \
    libzip \
    openldap \
    # Build dependencies (will remove later)
    && apk add --no-cache --virtual .build-deps \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libzip-dev \
    openldap-dev \
    && docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    --with-webp \
    && docker-php-ext-install -j$(nproc) \
    gd \
    bcmath \
    pdo_mysql \
    mysqli \
    zip \
    ldap \
    pcntl \
    && apk del .build-deps

# Copy composer files
COPY composer.json composer.lock ./

# Install composer dependencies (production only, optimized)
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-progress \
    --prefer-dist

# Stage 3: Final runtime image
FROM php:8.2-fpm-alpine

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    nginx \
    supervisor \
    mysql-client \
    # Runtime dependencies for PHP extensions (MUST KEEP)
    freetype \
    libjpeg-turbo \
    libpng \
    libwebp \
    libzip \
    icu-libs \
    libxml2 \
    oniguruma \
    openldap \
    # Build dependencies (will remove after compilation)
    && apk add --no-cache --virtual .build-deps \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libzip-dev \
    icu-dev \
    libxml2-dev \
    oniguruma-dev \
    openldap-dev \
    && docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    --with-webp \
    && docker-php-ext-install -j$(nproc) \
    gd \
    pdo_mysql \
    mysqli \
    zip \
    intl \
    opcache \
    bcmath \
    exif \
    mbstring \
    xml \
    ldap \
    pcntl \
    && apk del .build-deps \
    && rm -rf /var/cache/apk/*

# Configure PHP
COPY --chown=www-data:www-data <<EOF /usr/local/etc/php/conf.d/leantime.ini
memory_limit = 256M
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
date.timezone = UTC

opcache.enable = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 10000
opcache.revalidate_freq = 2
opcache.fast_shutdown = 1
EOF

# Configure Nginx
COPY --chown=root:root <<'EOF' /etc/nginx/nginx.conf
user www-data;
worker_processes auto;
pid /run/nginx.pid;
error_log /var/log/nginx/error.log warn;

events {
    worker_connections 1024;
}

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;

    log_format main '$remote_addr - $remote_user [$time_local] "$request" '
                    '$status $body_bytes_sent "$http_referer" '
                    '"$http_user_agent" "$http_x_forwarded_for"';

    access_log /var/log/nginx/access.log main;

    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    keepalive_timeout 65;
    types_hash_max_size 2048;
    client_max_body_size 100M;

    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss application/rss+xml font/truetype font/opentype application/vnd.ms-fontobject image/svg+xml;

    include /etc/nginx/conf.d/*.conf;
}
EOF

# Configure Nginx server block
COPY --chown=root:root <<'EOF' /etc/nginx/conf.d/default.conf
server {
    listen 8080;
    server_name _;
    root /var/www/html/public;
    index index.php index.html;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # Logging
    access_log /var/log/nginx/leantime-access.log;
    error_log /var/log/nginx/leantime-error.log;

    # Main location
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP handler
    location ~ \.php$ {
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        fastcgi_buffering off;
        fastcgi_read_timeout 300;
    }

    # Deny access to hidden files
    location ~ /\. {
        deny all;
    }

    # Static files caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
EOF

# Configure Supervisor
COPY --chown=root:root <<'EOF' /etc/supervisor/conf.d/supervisord.conf
[supervisord]
nodaemon=true
user=root
logfile=/var/log/supervisor/supervisord.log
pidfile=/var/run/supervisord.pid

[program:php-fpm]
command=php-fpm -F
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
autorestart=true
startretries=3

[program:nginx]
command=nginx -g 'daemon off;'
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
autorestart=true
startretries=3
EOF

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY --chown=www-data:www-data . .

# Copy vendor from composer stage
COPY --from=composer-builder --chown=www-data:www-data /build/vendor ./vendor

# Copy built frontend assets from frontend-builder stage
COPY --from=frontend-builder --chown=www-data:www-data /build/public/dist ./public/dist
COPY --from=frontend-builder --chown=www-data:www-data /build/blocklist.json ./blocklist.json

# Copy mix-manifest.json if it exists, otherwise create empty one
COPY --from=frontend-builder --chown=www-data:www-data /build/mix-manifest.json ./mix-manifest.json
RUN if [ -f ./mix-manifest.json ]; then \
        mv ./mix-manifest.json ./public/mix-manifest.json; \
        echo "Moved mix-manifest.json to public/"; \
    else \
        echo '{}' > ./public/mix-manifest.json; \
        echo "Created empty mix-manifest.json"; \
    fi && \
    chown www-data:www-data ./public/mix-manifest.json

# Create necessary directories and set permissions
RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    userfiles \
    public/userfiles \
    && chown -R www-data:www-data \
    storage \
    bootstrap/cache \
    userfiles \
    public/userfiles \
    && chmod -R 775 \
    storage \
    bootstrap/cache \
    userfiles \
    public/userfiles

# Create entrypoint script
COPY --chown=root:root <<'EOF' /entrypoint.sh
#!/bin/sh
set -e

echo "Starting Leantime container..."

# Wait for database to be ready
if [ -n "$LEAN_DB_HOST" ]; then
    echo "Waiting for database at $LEAN_DB_HOST:${LEAN_DB_PORT:-3306}..."
    timeout=60
    while ! nc -z "$LEAN_DB_HOST" "${LEAN_DB_PORT:-3306}" 2>/dev/null; do
        timeout=$((timeout - 1))
        if [ $timeout -le 0 ]; then
            echo "ERROR: Database connection timeout"
            exit 1
        fi
        echo "Waiting for database... ($timeout seconds remaining)"
        sleep 1
    done
    echo "Database is ready!"
fi

# Create .env file if it doesn't exist
if [ ! -f /var/www/html/config/.env ]; then
    echo "Creating config/.env file..."
    
    # Use provided environment variables or defaults
    cat > /var/www/html/config/.env <<ENVEOF
## Minimum Configuration
LEAN_APP_URL=${LEAN_APP_URL:-http://localhost:8080}
LEAN_APP_DIR=${LEAN_APP_DIR:-}
LEAN_DEBUG=${LEAN_DEBUG:-0}

## Database
LEAN_DB_HOST=${LEAN_DB_HOST:-leantime_db}
LEAN_DB_USER=${LEAN_DB_USER:-leantime}
LEAN_DB_PASSWORD=${LEAN_DB_PASSWORD:-leantime}
LEAN_DB_DATABASE=${LEAN_DB_DATABASE:-leantime}
LEAN_DB_PORT=${LEAN_DB_PORT:-3306}

## Session Management
LEAN_SESSION_PASSWORD=${LEAN_SESSION_PASSWORD:-$(openssl rand -base64 32)}
LEAN_SESSION_EXPIRATION=${LEAN_SESSION_EXPIRATION:-28800}
LEAN_SESSION_SECURE=${LEAN_SESSION_SECURE:-false}

## Default Settings
LEAN_SITENAME=${LEAN_SITENAME:-Leantime}
LEAN_LANGUAGE=${LEAN_LANGUAGE:-en-US}
LEAN_DEFAULT_TIMEZONE=${LEAN_DEFAULT_TIMEZONE:-UTC}
LEAN_LOG_PATH=${LEAN_LOG_PATH:-}

## File Uploads
LEAN_USER_FILE_PATH=${LEAN_USER_FILE_PATH:-userfiles/}
LEAN_DB_BACKUP_PATH=${LEAN_DB_BACKUP_PATH:-backupdb/}

## Email
LEAN_EMAIL_RETURN=${LEAN_EMAIL_RETURN:-}
LEAN_EMAIL_USE_SMTP=${LEAN_EMAIL_USE_SMTP:-false}

## Redis
LEAN_USE_REDIS=${LEAN_USE_REDIS:-false}
ENVEOF

    chown www-data:www-data /var/www/html/config/.env
    chmod 600 /var/www/html/config/.env
    echo "config/.env created successfully"
else
    echo "config/.env already exists, skipping creation"
fi

# Clear cache
echo "Clearing cache..."
rm -rf /var/www/html/storage/framework/cache/* || true
rm -rf /var/www/html/storage/framework/sessions/* || true
rm -rf /var/www/html/storage/framework/views/* || true
rm -rf /var/www/html/bootstrap/cache/*.php || true

echo "Leantime initialization complete!"
echo "Starting services..."

# Start supervisord
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
EOF

RUN chmod +x /entrypoint.sh

# Create directory for supervisor logs
RUN mkdir -p /var/log/supervisor

# Expose port
EXPOSE 8080

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=40s --retries=3 \
    CMD wget --no-verbose --tries=1 --spider http://localhost:8080/healthCheck.php || exit 1

# Set entrypoint
ENTRYPOINT ["/entrypoint.sh"]