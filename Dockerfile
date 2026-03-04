FROM webdevops/php-nginx:8.1-alpine

# Install PostgreSQL extensions and client with redundant mirrors and manual dependency management
RUN printf "https://dl-cdn.alpinelinux.org/alpine/v3.21/main\nhttps://dl-cdn.alpinelinux.org/alpine/v3.21/community\nhttps://uk.alpinelinux.org/alpine/v3.21/main\nhttps://uk.alpinelinux.org/alpine/v3.21/community\nhttps://dl-4.alpinelinux.org/alpine/v3.21/main\nhttps://dl-4.alpinelinux.org/alpine/v3.21/community" > /etc/apk/repositories && \
    apk update && \
    apk add --no-cache \
    autoconf \
    dpkg-dev \
    dpkg \
    file \
    g++ \
    gcc \
    libc-dev \
    make \
    pkgconf \
    re2c \
    postgresql-dev \
    postgresql-client && \
    docker-php-ext-install pdo_pgsql && \
    apk del autoconf dpkg-dev dpkg file g++ gcc libc-dev make pkgconf re2c

# Set working directory
WORKDIR /app

# Environment variables for Nginx
ENV WEB_DOCUMENT_ROOT=/app
ENV WEB_DOCUMENT_INDEX=index.php
ENV PHP_DISP_ERRORS=1

# Copy project files
COPY . /app/

# Make migration script executable
RUN chmod +x /app/scripts/run-migrations.sh

# Remove default nginx welcome pages to avoid confusion
RUN rm -rf /usr/share/nginx/html/* && \
    rm -f /etc/nginx/conf.d/default.conf

# Re-ensure permissions
RUN chown -R application:application /app

# Set custom entrypoint that runs migrations
ENTRYPOINT ["/app/scripts/docker-entrypoint.sh"]
