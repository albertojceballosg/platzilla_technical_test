<?php
/**
 * Fix temporal para error "Commands out of sync"
 * 
 * Este archivo debe incluirse al inicio del flujo de save de orden_de_trabajo
 * para limpiar cualquier resultado pendiente de la conexión MySQL.
 */

/**
 * Limpia resultados pendientes de la conexión MySQL
 * 
 * @param PearDatabase $adb
 * @return int Número de resultados limpiados
 */
function pearDatabase_FlushResults($adb) {
    $flushed = 0;
    // Verificar si hay resultados pendientes y consumirlos
    if (isset($adb->database) && is_object($adb->database)) {
        $conn = $adb->database;
        
        // Si es mysqli, intentar consumir resultados pendientes
        if ($conn instanceof mysqli) {
            while ($conn->more_results()) {
                $conn->next_result();
                if ($result = $conn->store_result()) {
                    $result->free();
                    $flushed++;
                }
            }
        }
    }
    
    // Log si se encontraron resultados pendientes
    if ($flushed > 0) {
        $logMsg = sprintf("[PEARDB_FLUSH] Limpiados %d resultados pendientes desde: %s\n", 
            $flushed, 
            (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'CLI')
        );
        error_log($logMsg, 3, 'logs/commands_out_of_sync.log');
    }
    
    return $flushed;
}

/**
 * Wrapper seguro para pquery que hace flush antes de ejecutar
 * 
 * @param PearDatabase $adb
 * @param string $sql
 * @param array $params
 * @return mixed
 */
function pearDatabase_SafePquery($adb, $sql, $params = array()) {
    // Intentar limpiar resultados pendientes
    pearDatabase_FlushResults($adb);
    
    // Ejecutar la consulta original
    return $adb->pquery($sql, $params);
}
