FROM php:8.2-fpm

# Argumentos definidos en docker-compose.yml
ARG user=laravel
ARG uid=1000

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    nginx \
    supervisor \
    libfcgi-bin

# Limpiar caché
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar extensiones de PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Obtener la última versión de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Crear usuario del sistema para ejecutar comandos de Composer y Artisan
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Configurar directorio de trabajo
WORKDIR /var/www

# Copiar archivos de la aplicación
COPY . /var/www

# Copiar archivos de configuración
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/default.conf /etc/nginx/sites-available/default
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

# Crear directorios de logs necesarios
RUN mkdir -p /var/log/supervisor && \
    touch /var/log/supervisor/supervisord.log && \
    chmod -R 755 /var/log/supervisor

# Dar permisos
RUN chown -R $user:www-data /var/www
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Crear script de healthcheck para PHP-FPM
RUN echo '#!/bin/bash\nCGI_FCGI_ENV="SCRIPT_NAME=/ping SCRIPT_FILENAME=/ping REQUEST_METHOD=GET" cgi-fcgi -bind -connect 127.0.0.1:9000 || exit 1' > /usr/local/bin/php-fpm-healthcheck && \
    chmod +x /usr/local/bin/php-fpm-healthcheck

# No cambiar a usuario no-root aquí, supervisord necesita root
# USER $user se configurará dentro de supervisord para los procesos específicos

# Exponer puerto 9000 y iniciar php-fpm
EXPOSE 9000
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
