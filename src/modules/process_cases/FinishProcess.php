<?php
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/EntityUtils.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/utils.php');
	
	global $adb, $current_user, $current_module;
	
	try {
		$caseId = PlatzillaUtils::purify ($_POST, 'caseid');
		
		if (empty ($caseId)) {
			throw new Exception ('Registro no encontrado');
		}
		
		$adb->pquery (
			'UPDATE vtiger_process_cases SET finish_process=? WHERE case_number=?',
			array (1, $caseId)
		);
		
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json');
		echo json_encode(array('error' => 'OK'));
	} catch (Exception $e) {
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json');
		echo json_encode (array ('error' => $e->getMessage ()));
	}
	exit();
