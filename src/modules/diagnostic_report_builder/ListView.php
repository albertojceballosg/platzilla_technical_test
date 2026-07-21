<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/diagnostic_report_builder/lib/DiagnosticReportBuilderHelper.class.php');
	
	global $adb, $current_user, $mod_strings;
	
	$smarty = new vtigerCRM_Smarty ();
	if (!is_admin ($current_user)) {
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('ICON_URL', vtiger_imageurl ('denied.gif', $theme));
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}
	
	$smarty->assign ('DIAGNOSTIC_REPORT', DiagnosticReportBuilderHelper::getInstance ($adb, $platform)->fetchDiagnosticReportBuilder (true));
	$smarty->assign ('MOD', $mod_strings);
	if (isset ($_SESSION ['flashmessage'])) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		unset ($_SESSION ['flashmessage']);
	}
	$smarty->display ('modules/diagnostic_report_builder/ListView.tpl');
