<?php
	require_once ('include/utils/utils.php');
	require_once ('modules/calculated_fields/CalculatedFields.class.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');
	require_once ('Smarty_setup.php');
	require_once ('vtlib/Vtiger/Utils.php');
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

	global $adb, $current_language, $current_user;

	try {
		$platform            = $_SESSION ['plat'];
		$objCalculatedFields = new CalculatedFieldsUtils ($adb, $platform);
		$smarty              = new vtigerCRM_Smarty ();
		$calculatedSystemId  = SettingsUtils::purify ($_REQUEST, 'calculatedSystemId');
		if ($calculatedSystemId != null) {
			$calculatedSystemData = $objCalculatedFields->getCalculateSystemDataById ($calculatedSystemId);
			$condition = "f.typeofdata LIKE 'N%' AND";
			if (!empty($calculatedSystemData->getCalculatedData ())) {
				$calculatedDataGroup = json_decode (str_replace('&quot;', '"', $calculatedSystemData->getCalculatedData ()), true);
			} else {
				$calculatedDataGroup = $calculatedSystemData->getCalculatedData ();
			}
			$modules[]      = $calculatedSystemData->getModuleName ();
			$relatedModules = $objCalculatedFields->getRelatedModulesByName ($calculatedSystemData->getModuleName ());
			foreach ($relatedModules as $relation) {
				$modules [] = $relation ['name'];
			}
			$smarty->assign('MWNF', $objCalculatedFields->getModulesForCalculations ());
			$smarty->assign('MODULE_NAME', $calculatedSystemData->getModuleName ());
			$smarty->assign('MODULE_FIELD', $objCalculatedFields->getColumnsByModule ($modules, $condition));
			$smarty->assign('CALCULATED_DATA', $calculatedDataGroup);
		}
		$smarty->assign ('MOD', return_module_language ($current_language, 'calculated_fields'));
		$smarty->assign ('ACF', $objCalculatedFields->getAllCalculateFields ());
		$smarty->display ('modules/calculated_fields/CreateCalculatedSystem.tpl');
	} catch (Exception $e) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $e->getMessage ());
		unset ($_SESSION ['flashmessage']);
		header ('Location: index.php?module=calculated_fields&action=index&parenttab=Settings');
	}
