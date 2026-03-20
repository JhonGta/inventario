FROM php:8.1-cli

# Instalar extensiones necesarias
RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /app

COPY . .

EXPOSE 8080

CMD ["php", "-S", "0.0.0.0:${PORT:-8080}", "-t", "."]
