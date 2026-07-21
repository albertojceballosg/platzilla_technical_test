<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/Settings/lib/ConfigApplicationsHelper.class.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb, $current_language;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	if (isset ($_SESSION ['application-error'])) {
		$error = SettingsUtils::purify ($_SESSION, 'application-error');
		unset ($_SESSION ['application-error']);
	} else {
		$error = null;
	}

	if (isset ($_SESSION ['application-data'])) {
		$application = SettingsUtils::purify ($_SESSION, 'application-data');
		unset ($_SESSION ['application-data']);
	} else {
		$application = null;
	}

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('CATEGORIES', ConfigApplicationsHelper::getActiveApplicationCategories ($adb));
	$smarty->assign ('MOD', return_module_language ($current_language, 'Settings'));
	$smarty->assign ('MODULES', ConfigApplicationsHelper::getVisibleModules ($adb));
	if ($application) {
		$smarty->assign ('APPLICATION', $application);
	}
	if ($error) {
		$smarty->assign ('ERROR', $error);
	}
	$smarty->display ('Settings/CreateApp.tpl');
