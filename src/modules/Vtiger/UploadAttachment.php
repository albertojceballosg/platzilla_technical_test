<?php
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/AttachmentsUtils.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Courses/lib/CoursesHelper.php');

	global $adb, $current_user, $currentModule, $upload_badext;

	try {
		$entityId = PlatzillaUtils::purify ($_POST, 'entityid');
		$fileData = PlatzillaUtils::purify ($_POST, 'filedata');
		$fileName = PlatzillaUtils::purify ($_POST, 'filename');
		$reportId = PlatzillaUtils::purify ($_POST, 'reportid', 0);
		
		$attachment   = array (
			'data'     => $fileData,
			'filename' => $fileName,
			'name'     => $fileName,
		);
		
		// Si hay reportId, usar el método específico para reportes
		if (!empty($reportId) && $currentModule == 'Calendar') {
			$data = AttachmentsUtils::saveActivityReportAttachment ($adb, $entityId, $reportId, $currentModule, $current_user->id, $attachment, $upload_badext);
		} else {
			$data = AttachmentsUtils::saveEntityAttachment ($adb, $entityId, $currentModule, $current_user->id, $attachment, $upload_badext);
		}
		
		if ($currentModule == 'Courses' && isset ($data['attachmentid'])) {
			$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
			CoursesHelper::saveExercisesAttachment ($masterAdb, $adb, $entityId, $data['attachmentid'], $current_user->id);
		} else if (!isset ($data['attachmentid'])) {
			throw new Exception ('Error al guardar el archivo');
		}

		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json');
		echo json_encode ($data);
	} catch (Exception $e) {
		header ('HTTP/1.1 400 Bad request');
		header ('Content-Type: application/json');
		echo json_encode ($e->getMessage ());
	}
	exit ();
