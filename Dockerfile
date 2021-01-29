FROM php:apache

COPY app/ /app/

COPY vhost.conf /etc/apache2/sites-available/000-default.conf

RUN chown -R www-data:www-data /app \
 && a2enmod rewrite
