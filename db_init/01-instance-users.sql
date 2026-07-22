-- ============================================================================
-- Provisión de usuarios MySQL por instancia (arquitectura multi-instancia)
-- ============================================================================
-- Platzilla abre, además de la conexión principal (superuser), una conexión
-- POR INSTANCIA con credenciales derivadas del nombre de la instancia:
--
--     usuario  = 'usr_' + <instancia>
--     password = md5('usr_' + <instancia>)
--
-- (ver src/customerPortal2/include.php, src/modules/notificaciones/…)
--
-- En producción esos usuarios existen; en el entorno local hay que crearlos.
-- Sin esto, al migrar el driver a mysqli el flujo autenticado falla con
-- "Access denied for user 'usr_madre'".
--
-- Este script lo ejecuta MySQL automáticamente en el primer arranque
-- (docker-entrypoint-initdb.d). Se corre antes que el dump por orden alfabético;
-- no importa, porque MYSQL_DATABASE ya creó el esquema pg_crm_madre.
-- ============================================================================

-- Instancia 'madre'  ->  usuario usr_madre / md5('usr_madre')
GRANT ALL PRIVILEGES ON pg_crm_madre.*
    TO 'usr_madre'@'%'
    IDENTIFIED BY '91a7e3885b1b6a5d47ebb3042bfbd84b';  -- = md5('usr_madre')

FLUSH PRIVILEGES;

-- NOTA: cada nueva instancia local necesitaría su propio GRANT análogo:
--   GRANT ALL PRIVILEGES ON <db_instancia>.* TO 'usr_<instancia>'@'%'
--       IDENTIFIED BY '<md5 de usr_<instancia>>';
