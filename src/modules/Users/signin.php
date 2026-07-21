<?php
	
	global $mod_strings,$app_strings,$theme,$bDlgModales;
	
	$_SESSION['plat'] = 'platzilla'; //Ojo si se llega acá se esta en platzilla

	$bDlgModales = true;
	$smartyDlg = new vtigerCRM_Smarty;
	$smartyDlg->assign("MODULE",$_REQUEST['module']);
	$smartyDlg->assign("MOD",$mod_strings);
	$smartyDlg->assign("APP",$app_strings);
	$smartyDlg->assign("THEME", $theme);
	
	if ($_REQUEST['status'] == 'exist')
		$smartyDlg->assign("MSG", 'El correo ya está registrado en Gestionar-Facil.com');
		
	if ($_SERVER['REQUEST_URI'] == '/crm' || $_SERVER['REQUEST_URI'] == '/CRM') {
		$smartyDlg->assign("REQUEST_URI", '1');
	}

	$smartyDlg->display('Settings/ModuleManager/wizardAplicacionEx.tpl');

?>