<?php
require_once('data/CRMEntity.php');
require_once('include/utils/CommonUtils.php');
require_once('include/utils/PlatzillaUtils.class.php');
require_once('user_privileges/default_module_view.php');

global $adb, $currentModule;
// MODIFICACIÓN 1: Cambie el tipo de respuesta a JSON para una mejor comunicación con el frontend.
header('Content-Type: application/json'); 

$response = [];

try {
    $entityId = PlatzillaUtils::purify($_POST, 'record');
    $operation = PlatzillaUtils::purify($_POST, 'operation');
    $relatedModule = PlatzillaUtils::purify($_POST, 'relatedmodule');
    $relatedRecordIds = PlatzillaUtils::purify($_POST, 'relatedrecords');

    // Respaldo en servidor: si no llega el módulo relacionado, dedúcelo del registro relacionado (vtiger_crmentity.setype)
    if ((empty($relatedModule) || $relatedModule === '') && !empty($relatedRecordIds)) {
        $firstId = is_array($relatedRecordIds) ? reset($relatedRecordIds) : $relatedRecordIds;
        if (!empty($firstId)) {
            $rs = $adb->pquery('SELECT setype FROM vtiger_crmentity WHERE crmid = ?', array($firstId));
            if ($rs && $adb->num_rows($rs) > 0) {
                $relatedModule = $adb->query_result($rs, 0, 'setype');
            }
        }
    }

    if (empty($entityId) || empty($relatedModule) || empty($relatedRecordIds)) {
        throw new Exception('Faltan parámetros requeridos.');
    }

    if ($relatedModule == 'Calendar') {
        // ... (la lógica de Calendar se mantiene igual, pero la adaptamos para la respuesta JSON)
		if ($operation == 'delete') {
				$questionMarks = str_repeat ('?, ', (count ($relatedRecordIds) - 1)) . '?';
				$adb->pquery ("DELETE FROM vtiger_seactivityrel WHERE crmid=? AND activityid IN ({$questionMarks})", array_merge (array ($entityId), $relatedRecordIds));
			} else {
				foreach ($relatedRecordIds as $relatedRecordId) {
					$adb->pquery ('INSERT IGNORE INTO vtiger_seactivityrel (crmid, activityid) VALUES (?, ?)', array ($entityId, $relatedRecordId));
				}
			}
    } else {
        $focus = CRMEntity::getInstance($currentModule);
        if ($operation == 'delete') {
            $relatedRecordId = $relatedRecordIds[0];

			// --- INICIO DE LA LÓGICA DE CORRECCIÓN ---

            // MODIFICACIÓN 2: Implementa la verificación para saber si la relación es un campo del módulo.
            // Esta consulta SQL busca campos de tipo 'relación' (uitype 10) que conecten los dos módulos.
            $isFieldRelation = false;
            $fieldCheckSql = "SELECT fieldname FROM vtiger_field 
                              INNER JOIN vtiger_tab ON vtiger_tab.tabid = vtiger_field.tabid 
                              WHERE vtiger_tab.name = ? AND uitype = 10 
                                AND fieldname IN (SELECT fieldname FROM vtiger_fieldmodulerel WHERE module = ? AND relmodule = ?)";
            $result = $adb->pquery($fieldCheckSql, array($currentModule, $currentModule, $relatedModule));

            if ($adb->num_rows($result) > 0) {
				// Si existe el campo, verifica si su valor coincide con el registro a eliminar.
                $fieldName = $adb->query_result($result, 0, 'fieldname');
                $entityData = CRMEntity::getInstance($currentModule);
                $entityData->retrieve_entity_info($entityId, $currentModule);
                if (isset($entityData->column_fields[$fieldName]) && $entityData->column_fields[$fieldName] == $relatedRecordId) {
                    $isFieldRelation = true;
                }
            }
			// MODIFICACIÓN 3: Lógica condicional. Actúa según el tipo de relación.
            if ($isFieldRelation) {
				// Si es un campo, lanza una excepción con el mensaje de error requerido.
                throw new Exception("No será eliminada la relación con el registro relacionado, 
									pues es parte del contenido del registro. Si desea eliminar esta relación, 
									deberá hacerlo a través del campo correspondiente, no desde esta tarjeta de registros relacionados.");
            } else {
				// MODIFICACIÓN 4: Si no es un campo, prepara el mensaje de éxito personalizado.
                // Obtener el identificador ANTES de borrar la relación
                $identifierSql = "SELECT fieldidentifier FROM vtiger_entityname WHERE modulename = ?";
                $result = $adb->pquery($identifierSql, [$relatedModule]);
                $identifierField = 'id'; // Fallback
                if ($adb->num_rows($result) > 0) {
                     $identifierField = $adb->query_result($result, 0, 'fieldidentifier') ?: $adb->query_result($result, 0, 'fieldname');
                }
                
                $relatedFocus = CRMEntity::getInstance($relatedModule);
                $relatedFocus->retrieve_entity_info($relatedRecordId, $relatedModule);
                $recordIdentifierText = $relatedFocus->column_fields[$identifierField];

                // Proceder con la eliminación
                $focus->delete_related_module($currentModule, $entityId, $relatedModule, $relatedRecordIds);

                $response['status'] = 'success';
                $response['message'] = 'La relación con el registro "' . $recordIdentifierText . '" ha sido eliminada.';
            }
        } else {
            $focus->save_related_module($currentModule, $entityId, $relatedModule, $relatedRecordIds);
            $response['status'] = 'success';
            $response['message'] = 'Relación guardada correctamente.';
        }
    }
} catch (Exception $e) {
	// MODIFICACIÓN 5: Centraliza el manejo de errores para enviarlos como JSON.
    header('HTTP/1.1 400 Bad request');
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit();