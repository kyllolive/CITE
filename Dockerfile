FROM php:8.2-apache

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libicu-dev \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mysqli mbstring exif pcntl bcmath gd intl zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite headers

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Create writable directory with proper permissions
RUN chown -R www-data:www-data /var/www/html/writable \
    && chmod -R 775 /var/www/html/writable

# Configure Apache to point to public directory
RUN echo '<VirtualHost *:80>\n\
    ServerAdmin webmaster@localhost\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Install Composer dependencies
RUN composer install --no-interaction --optimize-autoloader

# Setup environment file with proper database configuration for Docker
RUN echo "CI_ENVIRONMENT = development\n\
app.baseURL = 'http://localhost:8080'\n\
\n\
database.default.hostname = mysql\n\
database.default.database = ci4_db\n\
database.default.username = ci4_user\n\
database.default.password = ci4_password\n\
database.default.DBDriver = MySQLi\n\
database.default.DBPrefix =\n\
database.default.port = 3306" > .env

# Make spark executable
RUN chmod +x spark

# Set proper permissions for Apache
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \; \
    && chmod -R 775 /var/www/html/writable

# Expose port 80
EXPOSE 80

CMD ["apache2-foreground"]