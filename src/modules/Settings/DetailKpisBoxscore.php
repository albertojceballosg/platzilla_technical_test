<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb, $app_strings, $current_language, $theme;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$kpiId = SettingsUtils::purify ($_REQUEST, 'record');

	$result = $adb->pquery ('SELECT * FROM vtiger_kpisboxscore WHERE kpisboxscoreid=?', array ($kpiId));
	if (($result) && ($adb->num_rows ($result) > 0)) {
		$kpi            = $adb->fetchByAssoc ($result);
		$kpi ['active'] = $kpi ['active'] == 1 ? 'Activa' : 'Inactiva';
		$kpi ['module'] = getTabIdLabelByName ($kpi ['module']);
	} else {
		$kpi = null;
	}

	$smarty = new vtigerCRM_smarty ();
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('KPI', $kpi);
	$smarty->assign ('MOD', return_module_language ($current_language, 'Settings'));
	$smarty->assign ('MODULE', 'Settings');
	$smarty->assign ('THEME', $theme);
	$smarty->display ('Settings/DetailKpisBoxscore.tpl');
