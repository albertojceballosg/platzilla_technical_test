<?php
/**
 * Endpoint AJAX para recalcular valores de un campo grid
 * 
 * Este script recalcula todos los campos calculados (uitype 2204) y 
 * filas summary (uitype 2203) de un campo grid específico.
 * 
 * Parámetros requeridos:
 * - fieldid: ID del campo grid a recalcular
 * 
 * Parámetros opcionales:
 * - batchsize: Tamaño del lote para procesamiento (default: 100)
 */

// Iniciar output buffering para capturar cualquier output no deseado
ob_start();

require_once('include/utils/GridFieldUtils.class.php');
require_once('modules/Settings/lib/SettingsUtils.class.php');

global $adb, $current_user;

// Validar permisos de administrador
if (!is_admin($current_user)) {
	header('HTTP/1.1 403 Forbidden');
	header('Content-Type: application/json');
	echo json_encode(array(
		'success' => false,
		'message' => 'No tiene permisos para realizar esta acción. Solo administradores pueden recalcular campos grid.'
	));
	exit();
}

try {
	// Obtener parámetros
	$fieldId = SettingsUtils::purify($_REQUEST, 'fieldid');
	$batchSize = SettingsUtils::purify($_REQUEST, 'batchsize');
	
	// Validar fieldId
	if (empty($fieldId) || !is_numeric($fieldId)) {
		throw new Exception('ID de campo no proporcionado o inválido');
	}
	
	// Establecer batchSize por defecto si no se proporciona
	if (empty($batchSize) || !is_numeric($batchSize)) {
		$batchSize = 100;
	} else {
		$batchSize = intval($batchSize);
		// Limitar batchSize entre 10 y 500
		if ($batchSize < 10) {
			$batchSize = 10;
		} elseif ($batchSize > 500) {
			$batchSize = 500;
		}
	}
	
	// Ejecutar recálculo
	$result = GridFieldUtils::recalculateGridField($adb, intval($fieldId), $batchSize);
	
	// Limpiar cualquier output previo y retornar resultado
	ob_end_clean();
	header('HTTP/1.1 200 OK');
	header('Content-Type: application/json; charset=UTF-8');
	echo json_encode($result, JSON_UNESCAPED_UNICODE);
	
} catch (Exception $e) {
	// Limpiar cualquier output previo y manejar errores
	ob_end_clean();
	header('HTTP/1.1 400 Bad Request');
	header('Content-Type: application/json; charset=UTF-8');
	echo json_encode(array(
		'success' => false,
		'message' => $e->getMessage(),
		'processed' => 0,
		'errors' => 1,
		'skipped' => 0,
		'total' => 0,
		'messages' => array('Error: ' . $e->getMessage())
	), JSON_UNESCAPED_UNICODE);
}

exit();
