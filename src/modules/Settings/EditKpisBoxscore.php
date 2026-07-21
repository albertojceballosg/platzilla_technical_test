<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/Settings/lib/KpisHelper.class.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb, $current_language;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$kpiId   = SettingsUtils::purify ($_REQUEST, 'record');
	$keyword = (isset ($_SESSION ['queryFiltroForModule'])) && (!empty ($_SESSION ['queryFiltroForModule'])) ? $_SESSION ['queryFiltroForModule'] : null;

	$result = $adb->pquery ('SELECT * FROM vtiger_kpisboxscore WHERE kpisboxscoreid=?', array ($kpiId));
	$kpi    = ($result) && ($adb->num_rows ($result) > 0) ? $adb->fetchByAssoc ($result) : null;

	$smarty = new vtigerCRM_smarty ();
	$smarty->assign ('KPI', $kpi);
	$smarty->assign ('MOD', return_module_language ($current_language, 'Settings'));
	$smarty->assign ('MODULESFREE', KpisHelper::getVisibleModulesData ($adb, $keyword));
	$smarty->display ('Settings/EditKpisBoxscore.tpl');
