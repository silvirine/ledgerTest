FROM php:8.3-fpm

# Install system dependencies and PostgreSQL extension
RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    unzip \
 && docker-php-ext-install pdo pdo_pgsql

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set the working directory to /var/www/html (i.e. your symfony project)
WORKDIR /var/www/html

# Adjust permissions if needed
RUN chown -R www-data:www-data /var/www/html
