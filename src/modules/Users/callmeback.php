<?php

	require_once('Smarty_setup.php');

	global $mod_strings,$app_strings,$theme,$bDlgModales;

	$bDlgModales = true;
	$smartyDlg = new vtigerCRM_Smarty;
	$smartyDlg->assign("MODULE",$_REQUEST['module']);
	$smartyDlg->assign("MOD",$mod_strings);
	$smartyDlg->assign("APP",$app_strings);
	$smartyDlg->assign("THEME", $theme);

	if (!isset($_SESSION['briefing'])) {
		include('modules/Home/CustomerView.php');
	} else {
		$smartyDlg->display('Settings/ModuleManager/wizardAplicacionEx.tpl');
	}

?>