# syntax=docker/dockerfile:1

FROM php:8.4-fpm-bookworm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    nginx \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    sqlite3 \
    libsqlite3-dev \
    unzip \
    curl \
    gnupg \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get install -y nodejs

# Install PHP extensions
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions gd intl zip bcmath pdo_sqlite

# Working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Set permissions for Laravel
RUN chmod -R 775 storage bootstrap/cache && \
    chown -R www-data:www-data /var/www/html

# Install PHP dependencies
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Install Node dependencies and build assets
RUN npm install --legacy-peer-deps
RUN npm run build

# Config Nginx
RUN echo "server { \n\
    listen 8080; \n\
    root /var/www/html/public; \n\
    index index.php index.html; \n\
    location / { \n\
        try_files \$uri \$uri/ /index.php?\$query_string; \n\
    } \n\
    location ~ \.php$ { \n\
        include fastcgi_params; \n\
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name; \n\
        fastcgi_pass 127.0.0.1:9000; \n\
    } \n\
}" > /etc/nginx/sites-available/default

# Entrypoint script
RUN echo "#!/bin/bash \n\
mkdir -p /var/www/html/storage/database \n\
touch /var/www/html/storage/database/database.sqlite \n\
chown -R www-data:www-data /var/www/html/storage \n\
chmod -R 775 /var/www/html/storage \n\
php artisan migrate --force \n\
php-fpm -D \n\
nginx -g 'daemon off;'" > /entrypoint.sh && chmod +x /entrypoint.sh

EXPOSE 8080

ENTRYPOINT ["/entrypoint.sh"]
