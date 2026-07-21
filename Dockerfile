FROM php:5.6-apache

# php:5.6-apache ya incluye las librerias base necesarias para mysql/mysqli.
# No se necesita apt-get para las extensiones criticas.

# Instalar extensiones PHP criticas para vtiger/adodb
# IMPORTANTE: 'mysql' es la extension legacy requerida por mysql_connect()
RUN docker-php-ext-install mysql mysqli pdo pdo_mysql

# Habilitar mod_rewrite de Apache para rutas/.htaccess
RUN a2enmod rewrite

# Configurar Apache: AllowOverride All para que funcionen los .htaccess
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Configuracion PHP recomendada para vtiger
RUN echo 'memory_limit = 1024M' >> /usr/local/etc/php/php.ini \
    && echo 'upload_max_filesize = 64M' >> /usr/local/etc/php/php.ini \
    && echo 'post_max_size = 64M' >> /usr/local/etc/php/php.ini \
    && echo 'max_execution_time = 300' >> /usr/local/etc/php/php.ini

# Copiar y configurar el entrypoint
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
