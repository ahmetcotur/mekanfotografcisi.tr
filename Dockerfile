FROM webdevops/php-nginx:8.1-alpine

# Set working directory
WORKDIR /app

# Environment variables for Nginx
ENV WEB_DOCUMENT_ROOT=/app
ENV WEB_DOCUMENT_INDEX=index.php
ENV PHP_DISP_ERRORS=1

# Copy project files
COPY . /app/

# Ensure permissions
RUN chown -R application:application /app

# The webdevops/php-nginx image automatically handles Nginx and PHP-FPM startup
