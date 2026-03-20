FROM php:8.1-cli

# Instalar extensiones necesarias (PDO MySQL + GD para TCPDF con PNG alpha)
RUN apt-get update \
	&& apt-get install -y --no-install-recommends \
		libpng-dev \
		libjpeg62-turbo-dev \
		libfreetype6-dev \
	&& docker-php-ext-configure gd --with-freetype --with-jpeg \
	&& docker-php-ext-install pdo pdo_mysql gd \
	&& rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY . .

EXPOSE 8080

CMD php -S 0.0.0.0:${PORT:-8080} -t .
