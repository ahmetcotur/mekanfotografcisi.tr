FROM webdevops/php-nginx:8.1-alpine

# Install PostgreSQL client for running migrations (Note: webdevops/php-nginx already includes pdo_pgsql)
RUN apk update && apk add --no-cache postgresql-client

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
