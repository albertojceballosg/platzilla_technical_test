<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/EntityCommentsUtils.class.php');

	global $adb, $app_strings, $current_user, $mod_strings, $theme, $site_URL;
	setBugSnag ($site_URL);

	$moduleName = PlatzillaUtils::purify ($_GET, 'formodule');
	$record     = PlatzillaUtils::purify ($_GET, 'record');

	try {
		if (empty ($moduleName)) {
			throw new Exception ('Módulo no identificado!');
		}

		if (empty ($record)) {
			throw new Exception ('Número de registro no identificado');
		}

		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('COMMENTS', EntityCommentsUtils::fetchComments ($adb, $record, $_SESSION ['plat']));
		$smarty->assign ('MODULE', $moduleName);
		$smarty->assign ('RECORD', $record);
	} catch (Exception $e) {
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('IS_ERROR', true);
	}
	$smarty->display ('modules/grid_view/BoxContenets/DetailViewComment.tpl');
