FROM php:5.6-apache

# php:5.6-apache ya incluye las librerias base necesarias para mysql/mysqli.
# No se necesita apt-get para las extensiones criticas.

# Instalar extensiones PHP criticas para vtiger/adodb
# IMPORTANTE: 'mysql' es la extension legacy requerida por mysql_connect()
RUN docker-php-ext-install mysql mysqli pdo pdo_mysql

# OPcache: sin el, PHP 5.6 recompila en CADA request los ficheros del legacy
# (utils.php ~7.5k lineas, ReportRun.php ~7.9k, etc), lo que hacia la navegacion
# lenta. Cachear el bytecode elimina esa recompilacion. Es la causa medida de la
# lentitud, no cambios de la app.
RUN docker-php-ext-install opcache

# Habilitar mod_rewrite de Apache para rutas/.htaccess
RUN a2enmod rewrite

# Configurar Apache: AllowOverride All para que funcionen los .htaccess
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Configuracion PHP recomendada para vtiger
RUN echo 'memory_limit = 1024M' >> /usr/local/etc/php/php.ini \
    && echo 'upload_max_filesize = 64M' >> /usr/local/etc/php/php.ini \
    && echo 'post_max_size = 64M' >> /usr/local/etc/php/php.ini \
    && echo 'max_execution_time = 300' >> /usr/local/etc/php/php.ini

# Config OPcache. max_accelerated_files alto porque vtiger tiene miles de ficheros.
# validate_timestamps=1 + revalidate_freq=2: revisa mtime cada 2s, asi los cambios
# en el bind-mount ./src se recogen solos sin rebuild (comodo en local/dev).
RUN { \
      echo 'opcache.enable=1'; \
      echo 'opcache.enable_cli=0'; \
      echo 'opcache.memory_consumption=256'; \
      echo 'opcache.interned_strings_buffer=16'; \
      echo 'opcache.max_accelerated_files=20000'; \
      echo 'opcache.validate_timestamps=1'; \
      echo 'opcache.revalidate_freq=2'; \
    } > /usr/local/etc/php/conf.d/zz-opcache.ini

# Copiar y configurar el entrypoint
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
