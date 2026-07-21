<?php
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Settings/lib/HelpSettingsHelper.class.php');

	global $adb, $current_user;

	try {
		$moduleName = PlatzillaUtils::purify ($_GET, 'modulename');

		if (!empty ($_SESSION ['platInstancia'])) {
			$applications = PlatformUtils::getApplicationsByUserRole ($adb, $current_user->column_fields ['roleid'], $moduleName);
		} else {
			$applications = PlatformUtils::getApplicationsByModuleName ($adb, $moduleName);
		}
		if (!empty ($applications)) {
			$applicationCodes = array ();
			foreach ($applications as $application) {
				$applicationCodes [] = $application ['app_code'];
			}
		} else {
			$applicationCodes = null;
		}

		$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
		$data      = array (

			'questions' => HelpSettingsHelper::fetchHelpQuestionsByModuleName ($masterAdb, $applicationCodes, $moduleName),
			'tips'      => HelpSettingsHelper::fetchHelpTips ($masterAdb),
			'tutorials' => HelpSettingsHelper::fetchHelpTutorials ($masterAdb),
			'usecases'  => HelpSettingsHelper::fetchHelpUseCases ($masterAdb),
		);
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json');
		echo json_encode ($data);
	} catch (Exception $e) {
		header ('HTTP/1.1 400 Bad request');
		header ('Content-Type: application/json');
		echo json_encode ($e->getMessage ());
	}
	exit ();
