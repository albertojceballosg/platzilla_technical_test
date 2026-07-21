<?php
	global $adb;
	$function = isset($_GET['function']) ? $_GET['function'] : '';
	
	if ($function == 'FETCH_TASK_EVIDENCES') {
		require_once ('include/utils/AttachmentsUtils.class.php');
		$activityId = isset($_GET['activityId']) ? intval($_GET['activityId']) : 0;
		$reportId = isset($_GET['reportId']) ? intval($_GET['reportId']) : 0;
		$attachments = array();
		
		// Si hay reportId, cargar adjuntos del reporte específico
		// Si no hay reportId, cargar todos los adjuntos de la tarea (comportamiento anterior)
		if ($reportId > 0) {
			$attachments = AttachmentsUtils::fetchActivityReportAttachments($adb, $reportId);
		} else if ($activityId > 0) {
			$attachments = AttachmentsUtils::fetchEntityAttachments($adb, $activityId);
		}
		
		header('Content-Type: application/json');
		echo json_encode(array(
			'success' => true,
			'attachments' => $attachments ? $attachments : array()
		));
		exit;
	}
	
	require_once ('modules/Vtiger/AjaxEditViewUtils.php');
