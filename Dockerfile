FROM php:8.2-apache

# Copy website
COPY ./www /var/www/html/

# Set correct index priority
RUN echo "DirectoryIndex index.html index.php" > /etc/apache2/conf-available/index.conf \
    && a2enconf index

# IMPORTANT: force server name (prevents silent crash)
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Enable rewrite
RUN a2enmod rewrite

# Fix permissions
RUN chown -R www-data:www-data /var/www/html

# 🔥 CRITICAL FIX: force Apache to listen on 8080 properly
RUN sed -i 's/Listen 80/Listen 0.0.0.0:8080/' /etc/apache2/ports.conf

# Make sure vhost uses correct port
RUN sed -i 's/:80/:8080/g' /etc/apache2/sites-available/000-default.conf

EXPOSE 8080

CMD ["apache2-foreground"]