#!/bin/bash
set -e

CONFIG_FILE="/var/www/html/config.inc.php"

# Corregir db_server de 'localhost' a 'db' (nombre del servicio Docker)
if [ -f "$CONFIG_FILE" ]; then
    echo "[entrypoint] Ajustando configuracion de BD en config.inc.php..."
    # Corregir el host
    sed -i "s/\$dbconfig\['db_server'\] = 'localhost'/\$dbconfig['db_server'] = 'db'/g" "$CONFIG_FILE"
    sed -i "s/\$dbconfig\['db_serverForNewDB'\] = 'localhost'/\$dbconfig['db_serverForNewDB'] = 'db'/g" "$CONFIG_FILE"
    sed -i "s/\$dbconfig\['db_serverForNewUsers'\] = 'localhost'/\$dbconfig['db_serverForNewUsers'] = 'db'/g" "$CONFIG_FILE"
    echo "[entrypoint] config.inc.php actualizado. DB host: db, DB name: pg_crm_madre"
fi

# Crear y asignar permisos a las carpetas de escritura de vtiger (Smarty, cache, logs...).
# Algunas no se versionan (estan en .gitignore), asi que en un clon limpio no existen:
# mkdir -p las crea si faltan y chmod garantiza que www-data pueda escribir.
echo "[entrypoint] Preparando carpetas de escritura..."
WRITABLE_DIRS="/var/www/html/Smarty/templates_c \
               /var/www/html/cache /var/www/html/cache/images \
               /var/www/html/logs /var/www/html/storage \
               /var/www/html/user_privileges /var/www/html/test"
mkdir -p $WRITABLE_DIRS
chmod -R 777 $WRITABLE_DIRS

# Iniciar Apache
exec apache2-foreground
