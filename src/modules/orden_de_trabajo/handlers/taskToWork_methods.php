<?php
// Métodos temporales para copiar a taskToWork.class.php

/**
 * Verifica las relaciones de una tarea para determinar si puede ser eliminada lógicamente
 * @param integer $activityId ID de la tarea
 * @param integer $currentWorkId ID del trabajo actual (para excluirlo del conteo)
 * @return array Array con información de relaciones
 */
public function checkTaskRelations($activityId, $currentWorkId = null) {
	if (empty($activityId)) {
		return array(
			'canDelete' => false,
			'canUnlink' => false,
			'hasReports' => false,
			'hasOtherRecords' => false,
			'supplierName' => null,
			'relations' => array(),
			'warnings' => array('ID de tarea inválido')
		);
	}
	
	$relations = array();
	$warnings = array();
	
	// 1. Verificar relaciones con otros registros (excluyendo el trabajo actual)
	$sql = 'SELECT sar.crmid, ce.setype
			FROM vtiger_seactivityrel sar
			INNER JOIN vtiger_crmentity ce ON ce.crmid = sar.crmid AND ce.deleted = 0
			WHERE sar.activityid = ?';
	$params = array($activityId);
	
	if (!empty($currentWorkId)) {
		$sql .= ' AND sar.crmid != ?';
		$params[] = $currentWorkId;
	}
	
	$result = $this->adb->pquery($sql, $params);
	if ($this->adb->num_rows($result) > 0) {
		$otherRelations = array();
		while ($row = $this->adb->fetchByAssoc($result, -1, false)) {
			// Obtener el label usando getEntityName
			$entityNames = getEntityName($row['setype'], $row['crmid']);
			$label = isset($entityNames[$row['crmid']]) ? $entityNames[$row['crmid']] : 'ID: ' . $row['crmid'];
			
			$otherRelations[] = array(
				'id' => $row['crmid'],
				'type' => $row['setype'],
				'label' => $label
			);
		}
		if (!empty($otherRelations)) {
			$relations['other_records'] = $otherRelations;
			$warnings[] = 'La tarea está relacionada con ' . count($otherRelations) . ' registro(s) adicional(es)';
		}
	}
	DatabaseUtils::closeResult($result);
	
	// 2. Verificar reportes de avance (BLOQUEAN eliminación)
	$result = $this->adb->pquery(
		'SELECT COUNT(*) as report_count FROM vtiger_activity_report WHERE activityid = ? AND deleted = 0',
		array($activityId)
	);
	$reportCount = 0;
	if ($this->adb->num_rows($result) > 0) {
		$reportCount = (int)$this->adb->query_result($result, 0, 'report_count');
	}
	DatabaseUtils::closeResult($result);
	
	if ($reportCount > 0) {
		$relations['activity_reports'] = $reportCount;
		$warnings[] = 'La tarea tiene ' . $reportCount . ' reporte(s) de avance y no puede ser eliminada';
	}
	
	// 3. Verificar relación con proveedor ejecutor (NO impide eliminación, solo informativo)
	$supplierName = null;
	$result = $this->adb->pquery(
		'SELECT srel.proveedoresid, prov.alias
		 FROM vtiger_supplieractivityrel srel
		 INNER JOIN vtiger_proveedores prov ON prov.proveedoresid = srel.proveedoresid
		 WHERE srel.activityid = ?',
		array($activityId)
	);
	if ($this->adb->num_rows($result) > 0) {
		$row = $this->adb->fetchByAssoc($result, -1, false);
		$supplierName = $row['alias'];
	}
	DatabaseUtils::closeResult($result);
	
	// Determinar si puede ser eliminada/desvinculada
	// REGLA 1 y 2: Solo trabajo (con o sin ejecutor) -> Eliminar completamente
	// REGLA 3: Tiene reportes -> BLOQUEAR eliminación
	// REGLA 4: Tiene otras relaciones -> Solo desvincular
	$hasReports = $reportCount > 0;
	$hasOtherRecords = !empty($relations['other_records']);
	$canDelete = !$hasOtherRecords && !$hasReports;
	$canUnlink = !$hasReports;
	
	return array(
		'canDelete' => $canDelete,
		'canUnlink' => $canUnlink,
		'hasReports' => $hasReports,
		'hasOtherRecords' => $hasOtherRecords,
		'supplierName' => $supplierName,
		'relations' => $relations,
		'warnings' => $warnings
	);
}

/**
 * Elimina una tarea de un trabajo, con eliminación lógica si no tiene otras relaciones
 * @param integer $activityId ID de la tarea
 * @param integer $workId ID del trabajo
 * @return array Resultado de la operación
 */
public function deleteTaskFromWork($activityId, $workId) {
	if (empty($activityId) || empty($workId)) {
		return array(
			'success' => false,
			'message' => 'Parámetros inválidos'
		);
	}
	
	// Verificar relaciones
	$relationCheck = $this->checkTaskRelations($activityId, $workId);
	
	// REGLA 3: Si tiene reportes de avance, BLOQUEAR eliminación
	if ($relationCheck['hasReports']) {
		return array(
			'success' => false,
			'message' => 'No se puede eliminar una tarea que tiene reportes de avance',
			'blocked' => true,
			'reason' => 'reports'
		);
	}
	
	// Eliminar relación con el trabajo
	$this->adb->pquery(
		'DELETE FROM vtiger_seactivityrel WHERE crmid=? AND activityid=?',
		array($workId, $activityId)
	);
	
	$message = 'Relación con el trabajo eliminada';
	$logicallyDeleted = false;
	
	// REGLA 1 y 2: Si no tiene otras relaciones (con o sin ejecutor), eliminar lógicamente
	if ($relationCheck['canDelete']) {
		$this->adb->pquery(
			'UPDATE vtiger_crmentity SET deleted=1 WHERE crmid=?',
			array($activityId)
		);
		$message = 'Tarea eliminada completamente del sistema';
		$logicallyDeleted = true;
	}
	
	return array(
		'success' => true,
		'message' => $message,
		'logicallyDeleted' => $logicallyDeleted,
		'hasOtherRecords' => $relationCheck['hasOtherRecords'],
		'relations' => $relationCheck['relations'],
		'warnings' => $relationCheck['warnings']
	);
}
