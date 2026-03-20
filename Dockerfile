FROM php:8.1-apache

# Instalar extensiones necesarias
RUN docker-php-ext-install pdo pdo_mysql

# Desactivar módulos conflictivos editando los archivos .conf
RUN sed -i 's/^LoadModule mpm_event_module.*/# &/' /etc/apache2/mods-enabled/mpm_event.conf 2>/dev/null || true && \
    sed -i 's/^LoadModule mpm_worker_module.*/# &/' /etc/apache2/mods-enabled/mpm_worker.conf 2>/dev/null || true && \
    sed -i 's/^LoadModule mpm_prefork_module/LoadModule mpm_prefork_module/' /etc/apache2/mods-available/mpm_prefork.conf 2>/dev/null || true

# Habilitar solo prefork
RUN rm -f /etc/apache2/mods-enabled/mpm_*.load && \
    ln -s /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load

# Habilitar mod_rewrite
RUN a2enmod rewrite

WORKDIR /var/www/html

COPY . .

EXPOSE 80

CMD ["apache2-foreground"]
