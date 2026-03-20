FROM php:8.1-apache

# Instalar extensiones necesarias
RUN docker-php-ext-install pdo pdo_mysql

# Deshabilitar MPMs conflictivos y habilitar solo prefork
RUN a2dismod mpm_event && a2enmod mpm_prefork

# Habilitar mod_rewrite para .htaccess
RUN a2enmod rewrite

WORKDIR /var/www/html

COPY . .

EXPOSE 80

CMD ["apache2-foreground"]
