FROM php:8.2-fpm-alpine

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
        nginx \
        supervisor \
        mysql-client \
    && docker-php-ext-install mysqli pdo pdo_mysql

# Create non-root application user
RUN addgroup -g 1001 -S appgroup && adduser -u 1001 -S appuser -G appgroup

WORKDIR /var/www/html

# Copy application source (excluding secrets – see .dockerignore)
COPY --chown=appuser:appgroup . .

# Ensure log directory exists and is writable
RUN mkdir -p logs && chown appuser:appgroup logs && chmod 750 logs

# Copy nginx and supervisor configs
COPY docker/nginx.conf      /etc/nginx/nginx.conf
COPY docker/default.conf    /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf

# Expose HTTP
EXPOSE 80

USER appuser

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
