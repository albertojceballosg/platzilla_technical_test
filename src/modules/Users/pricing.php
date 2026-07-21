<?php

	
	global $mod_strings,$app_strings,$theme,$bDlgModales;

	$smartyDlg = new vtigerCRM_Smarty;
	$smartyDlg->assign("MODULE",$_REQUEST['module']);
	$smartyDlg->assign("MOD",$mod_strings);
	$smartyDlg->assign("APP",$app_strings);
	$smartyDlg->assign("THEME", $theme);
	$smartyDlg->assign("BRIEFING", $_SESSION['briefing']);
	$smartyDlg->assign("APPNAME", 'CRM-Fácil');
	
	
	$smartyDlg->display('pricing.tpl');

?>