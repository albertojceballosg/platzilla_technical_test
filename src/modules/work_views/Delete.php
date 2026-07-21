<?php
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/work_views/lib/WorksViewHelper.class.php');
	
	global $adb, $current_user;
	
	try {
		if (!is_admin ($current_user)) {
			throw new Exception ('Acceso denegado');
		}
		
		$viewId = PlatzillaUtils::purify ($_POST, 'record');
		
		WorksViewHelper::deleteWorkView ($adb, $current_user->id, $viewId);
		
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'La vista de tareas ha sido eliminada',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	
	header ("Location: index.php?module=work_views&action=index&parenttab=Settings");

