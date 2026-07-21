<?php
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/work_views/lib/WorksViewHelper.class.php');
	
	global $adb, $current_user;
	
	$statusView = PlatzillaUtils::purify ($_POST, 'statusview');
	$viewId     = PlatzillaUtils::purify ($_POST, 'record', null);
	$viewName   = PlatzillaUtils::purify ($_POST, 'view');
	
	try {
		if (!is_admin ($current_user)) {
			throw new Exception ('No estás autorizado a realizar acciones de configuración');
		}
		
		WorksViewHelper::saveWorkView ($adb, WorksView::getInstance ()
			->setId ($viewId)
			->setView ($viewName)
			->setViewStatus ($statusView)
			->setFormUser ($current_user->id)
		);
		
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => (empty ($viewId)) ? 'La vista de trabajo ha sido guardado' : 'La vista de trabajo ha sido actualizada',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
			'data'    => !empty ($arguments) ? $arguments : null,
		);
		
	}
	header ("Location: index.php?module=work_views&action=index&parenttab=Settings");
