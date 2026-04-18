FROM php:8.2-apache

# Copy your actual website folder
COPY ./www /var/www/html/

# Set index.html as priority
RUN echo "DirectoryIndex index.html index.php" > /etc/apache2/conf-available/index.conf \
    && a2enconf index

# Change port to 8080 (Cloud Run requirement)
RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf /etc/apache2/sites-enabled/000-default.conf

# Permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Allow access
RUN echo '<Directory /var/www/html/> \
    AllowOverride All \
    Require all granted \
</Directory>' > /etc/apache2/conf-available/custom.conf \
    && a2enconf custom

EXPOSE 8080