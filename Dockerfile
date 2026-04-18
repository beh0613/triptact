FROM php:8.2-apache

# Copy website
COPY ./www /var/www/html/

# Make index priority
RUN echo "DirectoryIndex index.html index.php" > /etc/apache2/conf-available/index.conf \
    && a2enconf index

# IMPORTANT: force Apache to listen on 8080 properly
RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf \
    && sed -i 's/80/8080/g' /etc/apache2/sites-available/000-default.conf

# Force Apache to bind correctly for Cloud Run
ENV APACHE_RUN_PORT=8080
ENV APACHE_RUN_USER=www-data
ENV APACHE_RUN_GROUP=www-data

# Fix permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Enable rewrite (safe for routing)
RUN a2enmod rewrite

EXPOSE 8080

# IMPORTANT: run apache in foreground
CMD ["apache2-foreground"]