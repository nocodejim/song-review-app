# Dockerfile (Initial Placeholder - See full documentation for final version)
FROM php:8.2-apache

# Install necessary extensions and utilities
RUN apt-get update && apt-get install -y \
    git \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql zip

# Configure Apache (Example - may need adjustment)
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# Set working directory
WORKDIR /var/www/html

# Copy application code
# (This will be handled by volume mounts in docker-compose for development)

# Expose port
EXPOSE 80

# Enable Apache rewrite module (if using .htaccess)
RUN a2enmod rewrite

# (Add more instructions as needed)
