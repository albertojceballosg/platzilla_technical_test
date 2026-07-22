-- =============================================================================
-- Saneado de columnas ENUM con cadena vacía '' (compatibilidad MariaDB 10.5)
-- =============================================================================
-- El dump de MySQL 5.6 contiene 75 filas con '' (cadena vacía) en columnas ENUM
-- que no la admiten como valor válido. En MySQL 5.6 (sql_mode permisivo) eso se
-- almacenaba en silencio; en MariaDB 10.5 (sql_mode STRICT_TRANS_TABLES por
-- defecto) cualquier INSERT/UPDATE que escriba '' en estas columnas falla con
-- ERROR 1265 "Data truncated". Ver docs/COMPATIBILIDAD_MARIADB105.md.
--
-- Este script corrige los datos ya cargados dejándolos compatibles con strict
-- mode. Es IDEMPOTENTE (el WHERE col='' no reaparece tras corregir) y se ejecuta
-- automáticamente tras el dump gracias al prefijo 'zz-' (los scripts de
-- /docker-entrypoint-initdb.d corren en orden alfabético; 'zz-' ordena al final,
-- después de 'base-datos-platzilla.sql').
--
-- Reemplazo elegido por columna (respetando nulabilidad y DEFAULT reales):
--   vtiger_activity.planned_task    (nullable, default 'PLANNED_AND_RECORDED') -> su default
--   vtiger_activity.show_in_matrix  (nullable, default 'YES')                  -> su default
--   vtiger_courselessons.videotype  (NOT NULL, default 'VIMEO')                -> su default
--   vtiger_help_fields.videotype    (nullable, default NULL)                   -> NULL (desconocido)
-- =============================================================================

USE `pg_crm_madre`;

UPDATE vtiger_activity      SET planned_task   = 'PLANNED_AND_RECORDED' WHERE planned_task   = '';
UPDATE vtiger_activity      SET show_in_matrix = 'YES'                  WHERE show_in_matrix = '';
UPDATE vtiger_courselessons SET videotype      = 'VIMEO'               WHERE videotype      = '';
UPDATE vtiger_help_fields   SET videotype      = NULL                  WHERE videotype      = '';
