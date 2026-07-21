<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/ListViewUtils.php');
	require_once ('modules/Settings/AuditTrail.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb, $app_strings, $current_language, $theme;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$isAjaxRequest = !empty (SettingsUtils::purify ($_REQUEST, 'ajax')) ? true : false;
	$start         = SettingsUtils::purify ($_REQUEST, 'start', 1);
	$userId        = SettingsUtils::purify ($_REQUEST, 'userid');

	$result       = $adb->pquery ('SELECT * FROM vtiger_audit_trial WHERE userid=?', array ($userId));
	$totalRecords = $result ? $adb->num_rows ($result) : 0;
	$navigation   = getNavigationValues ($start, $totalRecords, 100);
	$summary      = "{$app_strings ['LBL_SHOWING']} {$navigation ['start']} - {$navigation ['end_val']} {$app_strings ['LBL_LIST_OF']} {$totalRecords}";
	$auditTrail   = new AuditTrail ();

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('LIST_ENTRIES', $auditTrail->getAuditTrailEntries ($userId, $navigation));
	$smarty->assign ('LIST_HEADER', $auditTrail->getAuditTrailHeader ());
	$smarty->assign ('MOD', return_module_language ($current_language, 'Settings'));
	$smarty->assign ('NAVIGATION', getTableHeaderNavigation ($navigation, '', 'Settings', 'ShowAuditTrail'));
	$smarty->assign ('RECORD_COUNTS', $summary);
	$smarty->assign ('THEME_PATH', "themes/{$theme}");
	$smarty->assign ('USERID', $userId);
	if ($isAjaxRequest) {
		$smarty->display ('ShowAuditTrailContents.tpl');
	} else {
		$smarty->display ('ShowAuditTrail.tpl');
	}
