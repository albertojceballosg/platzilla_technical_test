<?php
	require_once ('include/utils/CommonUtils.php');

	global $smarty;
	if ((!isset ($smarty)) || (!$smarty)) {
		require_once ('Smarty_setup.php');
		$smarty = new vtigerCRM_Smarty ();
	}

	$smarty->assign ('HEADERS', getHeaderArray (true));
	$smarty->display ('Header.menu.inc.tpl');
