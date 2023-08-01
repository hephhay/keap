#!/bin/bash

# Configure Apache to use .htaccess files
echo "<Directory \"/var/www/html\">" >> /etc/apache2/apache2.conf
echo "    AllowOverride All" >> /etc/apache2/apache2.conf
echo "</Directory>" >> /etc/apache2/apache2.conf

# Restart Apache to apply the changes
service apache2 restart