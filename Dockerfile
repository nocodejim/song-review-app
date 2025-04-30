# Dockerfile for Song Review App Development Environment (Final v1)

# Use the official PHP 8.2 image with Apache pre-installed.
# Using a specific version is good practice for consistency.
FROM php:8.2-apache

# --- Environment Variables ---
# Set Apache document root relative to the default WORKDIR (/var/www/html).
# This ensures Apache serves files from the 'public' subdirectory.
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

# --- System Dependencies & PHP Extensions ---
# Install necessary system packages and required PHP extensions.
# - git: Useful for version control tasks within the container if needed.
# - libzip-dev, zip, unzip: For handling zip archives (often needed by PHP libraries/Composer).
# - pdo, pdo_mysql: Required PHP Data Objects extensions for MySQL database access.
# Clean up apt cache afterwards to keep the final image size smaller.
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# --- Apache Configuration ---
# 1. Update the default Apache site configuration (000-default.conf)
#    to point DocumentRoot to the new public directory (/var/www/html/public).
#    The '${APACHE_DOCUMENT_ROOT}' variable is expanded here.
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# 2. Update Apache's main configuration (apache2.conf) Directory block
#    to refer to the new DocumentRoot path.
# 3. Change 'AllowOverride None' to 'AllowOverride All' within that Directory block.
#    This is crucial for allowing `.htaccess` files within the public directory
#    to function correctly (e.g., for URL rewriting, although not used in V1).
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf \
    && sed -ri -e '/<Directory \${APACHE_DOCUMENT_ROOT}>/,/<\/Directory>/ s!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf

# Enable the Apache rewrite module, commonly needed for frameworks and clean URLs.
RUN a2enmod rewrite

# --- Application Code ---
# Application code (public/, config/) will be mounted via docker-compose volumes
# during development, so no COPY command is needed here for the main app code.
# For a production build, you would typically COPY the application files here.

# --- Working Directory ---
# Set the working directory inside the container. Apache's default is /var/www/html.
WORKDIR /var/www/html

# --- Expose Port ---
# Inform Docker that the container listens on port 80 (standard HTTP).
# This port will be mapped to a host port (e.g., 8080) in docker-compose.yml.
EXPOSE 80

# --- Default Command ---
# The base php:apache image already sets the correct CMD to start Apache in the foreground.
# No need to override unless specific startup logic is required.
