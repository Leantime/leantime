FROM php:8.2-apache

COPY ./dev-apache2.conf /etc/apache2/apache2.conf
COPY ./dev-ports.conf /etc/apache2/ports.conf

# Ensure packages are available.
RUN apt-get update

RUN DEBIAN_FRONTEND=noninteractive \
    apt install -fqy \
    libonig-dev \
    libcurl4-openssl-dev \
    libxml2-dev \
    libxslt1-dev \
    libzip-dev \
    libjson-c-dev \
    libldap-dev \
    libargon2-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    apt-utils \
    vim \
    curl \
    sqlite3 \
    openssl \
    default-mysql-client \
    iputils-ping \
&& rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN pecl install xdebug

RUN docker-php-ext-configure gd --enable-gd --with-jpeg=/usr/include/ --with-freetype --with-jpeg
RUN docker-php-ext-install \
    gd \
    mysqli \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    pdo \
    bcmath \
    opcache \
    ldap \
    zip
RUN docker-php-ext-enable zip xdebug

COPY ./dev-apache-site.conf /etc/apache2/sites-enabled/000-default.conf

RUN openssl req -x509 -newkey rsa:4096 -keyout /etc/ssl/private/key.pem -out /etc/ssl/certs/cert.pem -days 365 -nodes -subj "/C=US/ST=NY/L=NY/O=ACME/OU=CD/CN=AcmeWPDeveloper"

RUN a2enmod ssl
RUN a2enmod rewrite

VOLUME /var/www/uploads

RUN mkdir -p /var/www/uploads \
 && chown -R www-data:www-data /var/www/uploads
