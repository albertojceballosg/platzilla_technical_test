<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/notifications/lib/NotificationUtils.class.php');
	require_once ('include/platzilla/Managers/PicklistManager.php');
	// Agregado por EB para integrar BUGSNAG - 20200213
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
	// Agregado por EB para integrar BUGSNAG - 20200213

	global $adb, $current_language;

	$function   = PlatzillaUtils::purify ($_REQUEST, 'function');
	$moduleName = PlatzillaUtils::purify ($_REQUEST, 'fld_module');

	if (empty ($function)) {
		exit ();
	}

	if ($function == 'getColumns') {
		if (empty ($moduleName)) {
			exit ();
		}
		echo json_encode (NotificationUtils::getColumnsByModule ($adb, $moduleName));
	} else if ($function == 'FETCH-PICKLIST') {
		try {
			$fieldName = PlatzillaUtils::purify ($_REQUEST, 'fieldname');
			$flModule  = PlatzillaUtils::purify ($_REQUEST, 'flmodule');
			if (empty ($fieldName)) {
				throw new Exception ('Campo lista no encontrado');
			}
			// Extract field name from table alias (e.g., "tq.status" -> "status", "vtiger_users.status" -> "status")
			if (strpos($fieldName, '.') !== false) {
				$parts = explode('.', $fieldName);
				$fieldName = end($parts);
			}
			$moduleTranslator = null;
			if (!empty($flModule)) {
				$moduleTranslator = return_module_language ($current_language, $flModule);
			}
			
			$pickList = PicklistManager::getInstance ($adb)->fetchPicklistByName ($fieldName, true);
			if (empty($pickList)) {
				throw new Exception ('Lista no encontrada');
			}
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('MOD', $moduleTranslator);
			$smarty->assign ('PICKLIST_VALUES', $pickList);
			$smarty->assign ('VALUE', null);
			
			$htmlOutput = $smarty->fetch ('utils/HTMLPickListOptions.tpl');
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $htmlOutput));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	}
	exit ();
