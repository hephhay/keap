FROM php:7.4-apache

# Install necessary PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable mod_rewrite module
RUN a2enmod rewrite

# Copy the application files to the container
COPY . /var/www/html/

# Set the working directory to the application directory
WORKDIR /var/www/html/

# Expose port 80
EXPOSE 80

# Configure Apache to use .htaccess files
RUN echo "<Directory \"/var/www/html\">" >> /etc/apache2/apache2.conf
RUN echo "    AllowOverride All" >> /etc/apache2/apache2.conf
RUN echo "</Directory>" >> /etc/apache2/apache2.conf

# Restart Apache to apply the changes
RUN service apache2 restart