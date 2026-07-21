<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/grid_view/lib/GridViewHelper.class.php');

	global $adb, $app_strings, $current_user, $mod_strings, $theme, $site_URL, $theme;

	$entityId   = PlatzillaUtils::purify ($_GET, 'record');
	$smarty = new vtigerCRM_Smarty ();
	try {
		if (empty ($entityId)) {
			throw new Exception ('No hay registro asociado!');
		}

		$smarty->assign ('TASK_ID',$activityId);
		$smarty->assign ('ID', $entityId);
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('REPORT', $report);
		$smarty->assign ('THEME', $theme);
	} catch (Exception $e) {
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
	}
	$smarty->display ('modules/grid_view/BoxContenets/EditRecordActivity.tpl');