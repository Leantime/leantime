FROM php:8.2-fpm-alpine AS composer-builder

WORKDIR /build

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN apk add --no-cache \
    freetype \
    libjpeg-turbo \
    libpng \
    libwebp \
    libzip \
    openldap \
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

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-progress \
    --prefer-dist

FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    nginx \
    supervisor \
    mysql-client \
    freetype \
    libjpeg-turbo \
    libpng \
    libwebp \
    libzip \
    icu-libs \
    libxml2 \
    oniguruma \
    openldap \
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

COPY --chown=root:root <<'EOF' /etc/nginx/conf.d/default.conf
server {
    listen 8080;
    server_name _;
    root /var/www/html/public;
    index index.php index.html;

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    access_log /var/log/nginx/leantime-access.log;
    error_log /var/log/nginx/leantime-error.log;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

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

    location ~ /\. {
        deny all;
    }

    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
EOF

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

WORKDIR /var/www/html

COPY --chown=www-data:www-data . .

COPY --from=composer-builder --chown=www-data:www-data /build/vendor ./vendor

RUN if [ ! -d "public/dist/js" ]; then \
        echo "ERROR: public/dist/js not found! Please run 'npx mix --production' locally first"; \
        exit 1; \
    fi && \
    chown -R www-data:www-data public/dist

RUN APP_SIZE=$(stat -c%s "public/dist/js/compiled-app.3.5.12.min.js" 2>/dev/null || echo "0") && \
    if [ "$APP_SIZE" -lt "150000" ]; then \
        echo "ERROR: compiled-app.js is too small ($APP_SIZE bytes). Expected >150KB"; \
        echo "Please run 'npx mix --production' locally before building Docker image"; \
        exit 1; \
    fi && \
    echo "✓ compiled-app.js size OK: $APP_SIZE bytes"

RUN if [ ! -f blocklist.json ]; then \
        echo "{}" > blocklist.json; \
    fi && \
    chown www-data:www-data blocklist.json

RUN if [ -f ./public/mix-manifest.json ]; then \
        echo "✓ mix-manifest.json found"; \
    elif [ -f ./mix-manifest.json ]; then \
        mv ./mix-manifest.json ./public/mix-manifest.json; \
        echo "✓ Moved mix-manifest.json to public/"; \
    else \
        echo "{}" > ./public/mix-manifest.json; \
        echo "✓ Created empty mix-manifest.json"; \
    fi && \
    chown www-data:www-data ./public/mix-manifest.json

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

COPY --chown=root:root <<'EOF' /entrypoint.sh
set -e

echo "Starting Leantime container..."

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

if [ ! -f /var/www/html/config/.env ]; then
    echo "Creating config/.env file..."
    
    cat > /var/www/html/config/.env <<ENVEOF
LEAN_APP_URL="${LEAN_APP_URL:-http://localhost:8080}"
LEAN_APP_DIR="${LEAN_APP_DIR:-}"
LEAN_DEBUG=${LEAN_DEBUG:-0}

LEAN_DB_HOST="${LEAN_DB_HOST:-leantime_db}"
LEAN_DB_USER="${LEAN_DB_USER:-leantime}"
LEAN_DB_PASSWORD="${LEAN_DB_PASSWORD:-leantime}"
LEAN_DB_DATABASE="${LEAN_DB_DATABASE:-leantime}"
LEAN_DB_PORT=${LEAN_DB_PORT:-3306}

LEAN_SESSION_PASSWORD="${LEAN_SESSION_PASSWORD:-$(openssl rand -base64 32)}"
LEAN_SESSION_EXPIRATION=${LEAN_SESSION_EXPIRATION:-28800}
LEAN_SESSION_SECURE=${LEAN_SESSION_SECURE:-false}

LEAN_SITENAME="${LEAN_SITENAME:-Leantime}"
LEAN_LANGUAGE="${LEAN_LANGUAGE:-en-US}"
LEAN_DEFAULT_TIMEZONE="${LEAN_DEFAULT_TIMEZONE:-UTC}"
LEAN_LOG_PATH="${LEAN_LOG_PATH:-}"

LEAN_USER_FILE_PATH="${LEAN_USER_FILE_PATH:-userfiles/}"
LEAN_DB_BACKUP_PATH="${LEAN_DB_BACKUP_PATH:-backupdb/}"

LEAN_EMAIL_RETURN="${LEAN_EMAIL_RETURN:-}"
LEAN_EMAIL_USE_SMTP=${LEAN_EMAIL_USE_SMTP:-false}

LEAN_USE_REDIS=${LEAN_USE_REDIS:-false}
ENVEOF

    chown www-data:www-data /var/www/html/config/.env
    chmod 600 /var/www/html/config/.env
    echo "config/.env created successfully"
else
    echo "config/.env already exists, skipping creation"
fi

echo "Clearing cache..."
rm -rf /var/www/html/storage/framework/cache/* || true
rm -rf /var/www/html/storage/framework/sessions/* || true
rm -rf /var/www/html/storage/framework/views/* || true
rm -rf /var/www/html/bootstrap/cache/*.php || true
rm -f /var/www/html/storage/framework/composerPaths.php || true
rm -f /var/www/html/storage/framework/viewPaths.php || true

echo "Leantime initialization complete!"
echo "Starting services..."

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
EOF

RUN chmod +x /entrypoint.sh

RUN mkdir -p /var/log/supervisor

EXPOSE 8080

HEALTHCHECK --interval=30s --timeout=10s --start-period=40s --retries=3 \
    CMD wget --no-verbose --tries=1 --spider http://localhost:8080/healthCheck.php || exit 1

ENTRYPOINT ["/entrypoint.sh"]