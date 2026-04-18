FROM php:8.2-apache

# Copy your project files into Apache directory
COPY . /var/www/html/

# Change Apache port from 80 → 8080 (required by Cloud Run)
RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf /etc/apache2/sites-enabled/000-default.conf

# Allow Apache to run
EXPOSE 8080