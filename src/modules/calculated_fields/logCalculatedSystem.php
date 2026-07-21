<?php
	require_once ('modules/Settings/lib/SettingsUtils.class.php');
	require_once ('modules/calculated_fields/CalculatedFields.class.php');
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

	global $adb, $current_language, $current_user;

	$platform            = $_SESSION ['plat'];
	$objCalculatedFields = new CalculatedFieldsUtils ($adb, $platform);
	$smarty              = new vtigerCRM_Smarty ();
	$calculatedSystemId  = SettingsUtils::purify ($_REQUEST, 'calculatedSystemId');
	$logFileHandle       = null;
	$calculationName     = 'No encontrado.';
	try {
		if ($calculatedSystemId != null) {
			$calculatedSystemData = $objCalculatedFields->getCalculateSystemDataById ($calculatedSystemId);
			$calculationName = $calculatedSystemData->getName ();
			$logFielName = 'calculo_sistema_id_' . $calculatedSystemId;
			$logFilePath = __DIR__ . "/../../{$platform}/logs/calculatedsystem/{$logFielName}.log";
			if (file_exists($logFilePath)) {
				$logFileHandle = fopen($logFilePath, 'r');
			}
		}
	} catch (Exception $e) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $e->getMessage ());
		unset ($_SESSION ['flashmessage']);
	}

	$smarty->assign ('MOD', return_module_language ($current_language, 'calculated_fields'));
	$smarty->assign ('LOG_FILE_HANDLE', $logFileHandle);
	$smarty->assign ('CS_ID', $calculatedSystemId);
	$smarty->assign ('CS_NAME', $calculationName);
	$smarty->display ('modules/calculated_fields/LogCalculatedSystem.tpl');
