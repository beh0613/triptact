FROM php:8.2-apache

# Copy website
COPY ./www /var/www/html/

# Set index priority
RUN echo "DirectoryIndex index.html index.php" > /etc/apache2/conf-available/index.conf \
    && a2enconf index

# Prevent Apache startup crash
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Enable rewrite module
RUN a2enmod rewrite

# Fix permissions
RUN chown -R www-data:www-data /var/www/html

# 🔥 CRITICAL FIX: FORCE LISTEN ON 8080
RUN sed -i 's/Listen 80/Listen 0.0.0.0:8080/' /etc/apache2/ports.conf

# Also update virtual host properly
RUN sed -i 's/:80/:8080/g' /etc/apache2/sites-available/000-default.conf

EXPOSE 8080

# MUST RUN FOREGROUND (Cloud Run requirement)
CMD ["apache2-foreground"]