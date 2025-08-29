FROM dunglas/frankenphp:1-php8.3-alpine AS base
RUN set -eux; \
    install-php-extensions mysqli pdo_mysql bcmath mbstring \
        exif pcntl gd opcache ldap zip 

RUN rm -rf /tmp/* /var/cache/apk/*

FROM base AS builder
RUN  apk add npm make zip tar
RUN set -eux; \
	install-php-extensions @composer 

FROM builder AS build

COPY ./ /build

WORKDIR /build

RUN make install-deps package

FROM base AS runner

COPY --from=build /build/target/leantime/ /app
ENV SERVER_NAME=:8080