FROM php:8.5-apache

COPY . /var/www/html/

RUN set -eux; \
    a2enmod headers; \
    printf '%s\n' 'ServerTokens Prod' 'ServerSignature Off' 'TraceEnable Off' > /etc/apache2/conf-available/hardening.conf; \
    a2enconf hardening; \
    chown -R www-data:www-data /var/www/html; \
    chmod -R 755 /var/www/html
