FROM php:8.2-apache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install PDO MySQL extensions
RUN docker-php-ext-install pdo pdo_mysql

# Copy project files
COPY . /var/www/html/

# Set file permissions for uploads directory
RUN mkdir -p /var/www/html/uploads && chmod -R 777 /var/www/html/uploads

# Expose port 80
EXPOSE 80

CMD ["apache2-foreground"]
