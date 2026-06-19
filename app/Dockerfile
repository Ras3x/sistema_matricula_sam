FROM php:8.2-apache

# Instalar dependencias del sistema y extensiones de PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Habilitar mod_rewrite de Apache para rutas amigables si es necesario
RUN a2enmod rewrite

# Configurar límites de carga de archivos (para vouchers y requisitos)
RUN echo "upload_max_filesize = 10M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 10M" >> /usr/local/etc/php/conf.d/uploads.ini

# Establecer el directorio de trabajo
WORKDIR /var/www/html

# Asegurar permisos correctos en uploads
RUN mkdir -p uploads/vouchers uploads/requisitos \
    && chmod -R 777 uploads
