<?php
	require_once ('include/utils/utils.php');
	require_once ('modules/calculated_fields/CalculatedFields.class.php');
	require_once ('modules/calculated_fields/lib/CalculatedFieldsHelper.class.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');
	require_once ('Smarty_setup.php');
	// Agregado por EB para integrar BUGSNAG - 20200326
	global $site_URL;
	require_once ('include/bugsnag-php-2.9.2/src/Bugsnag/Autoload.php');
	$bugsnag = new Bugsnag_Client('834d564193a48c47f138dc66d2cf5e83');
	$bugsnag->setAppVersion('1.0.0');
	if ($site_URL == 'https://apphome.platzillatest.com/') {
		$bugsnag->setReleaseStage('https://apphome.platzillatest.com/');
	} else if ($site_URL == 'https://app.platzilla.com/') {
		$bugsnag->setReleaseStage('https://app.platzilla.com/');
	} else {
		$bugsnag->setReleaseStage($site_URL);
	}
	$bugsnag->setErrorReportingLevel(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_WARNING & ~E_DEPRECATED);
	// Agregado por EB para integrar BUGSNAG - 20200326

	global $adb, $current_language;

	$platform = $_SESSION ['plat'];
	$smarty   = new vtigerCRM_Smarty ();
	try {
		$objCalculatedFields = new CalculatedFieldsUtils ($adb, $platform);
		$arrCalculatedFields = $objCalculatedFields->getAllCalculateFields ();
		$arrCalculatedSystem = $objCalculatedFields->getAllCalculateSystem ($current_user);
	} catch (Exception $e) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $e->getMessage ());
		unset ($_SESSION ['flashmessage']);
	}

	$activeTab           = SettingsUtils::purify ($_REQUEST, 'tab');

	if ($activeTab != null) {
		$smarty->assign ('TAB', $activeTab);
	}
	$smarty->assign ('ACF', $arrCalculatedFields);
	$smarty->assign ('ACS', $arrCalculatedSystem);
	$smarty->assign ('GRID_WITH_CALCULATED_FIELDS', CalculatedFieldsHelper::getModulesWithGridCalculatedFields ($adb));
	$smarty->assign ('MOD', return_module_language ($current_language, 'calculated_fields'));

	if (isset ($_SESSION ['flashmessage'])) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		unset ($_SESSION ['flashmessage']);
	}

	$smarty->display ('modules/calculated_fields/calculatedFields.tpl');
