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

# Iniciar Apache
exec apache2-foreground
