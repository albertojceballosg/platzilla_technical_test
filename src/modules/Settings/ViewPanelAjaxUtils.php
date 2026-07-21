<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Settings/lib/PanelViewHelper.class.php');
	
	global $adb, $current_user, $mod_strings;
	
	$smarty = new vtigerCRM_Smarty();
	if (!is_admin ($current_user)) {
		$smarty->assign ('IS_ADMIN', false);
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}
	
	$flModule     = PlatzillaUtils::purify ($_POST, 'fl_module');
	$moduleStatus = PlatzillaUtils::purify ($_POST, 'module_status');
	
	try {
		if (empty($flModule)) {
			throw new Exception ('No se ha seleccionado ningún módulo');
		}
		$tabStatus = ($moduleStatus == 'SHOW') ? 'HIDDEN' : 'SHOW';
		PanelViewHelper::setStatusModule ($adb, $flModule, $tabStatus, $current_user->id);
		$_SESSION ['flashmessage']['data'] = array (
					'iserror' => false,
					'message' => 'Se ha actualizado el módulo correctamente'
		);
		header ("Location: index.php?module=Settings&action=TaskViewPanel&parenttab=Settings");
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
		header ("Location: index.php?module=Settings&action=TaskViewPanel&parenttab=Settings");
	}
	exit ();
