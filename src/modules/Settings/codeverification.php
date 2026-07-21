<?php
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/backgroundtasks/lib/BackgroundTasksRunner.class.php');
	require_once ('modules/Settings/lib/CodeVerificationHelper.class.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $current_user;

	$mode = SettingsUtils::purify ($_REQUEST, 'mode');

	if (in_array ($mode, array ('codeverification', 'geturl'))) {
		$verificationCode = SettingsUtils::purify ($_REQUEST, 'codigo');
		$instanceName     = isset ($_SESSION ['plat']) ? $_SESSION ['plat'] : null;
		$adb              = AdbManager::getInstance ()->getMasterAdb ();

		if (CodeVerificationHelper::isValidVerificationCode ($adb, $instanceName, $verificationCode)) {
			CodeVerificationHelper::markInstanceAsVerified ($adb, $instanceName);
			$sw = 1;
		} else {
			$sw = 0;
		}
		header ("Location: index.php?module=Home&action=index&sw={$sw}");
	} else if ($mode == 'resendemail') {
		$instanceName     = isset ($_SESSION ['platInstancia']) ? $_SESSION ['platInstancia'] : null;
		$adb              = AdbManager::getInstance ()->getMasterAdb ();
		$instanceData     = CodeVerificationHelper::getInstanceData ($adb, $instanceName);
		if ($instanceData) {
			try {
				BackgroundTasksRunner::getInstance ($adb, $_SESSION ['plat'])->runManuallyTriggeredTask ('[SYS] - Enviar código de verificación', $instanceData ['instanceid']);
			} catch (Exception $e) {
				// Ignored
			}
		}
		header ('Location: index.php?module=Home&action=index');
	} else {
		header ('Location: index.php?module=Home&action=index');
	}
	exit ();
