<?php
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/notifications/lib/NotificationUtils.class.php');
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

	global $adb, $app_strings, $current_user, $mod_strings, $theme;

	$platform = $_SESSION ['plat'];
	try {
		$recordId = PlatzillaUtils::purify ($_POST, 'record');
		if (empty ($recordId)) {
			throw new Exception ('No has suministrado el ID de la notificación ha actualizar');
		}

		$isLocked = empty ($_SESSION ['platInstancia']) ? false : true;
		$message  = NotificationUtils::changeStatusNotification ($adb, $recordId, $isLocked, $platform);

		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => $message,
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	header ('Location: index.php?module=notifications&action=ListView&parenttab=Settings');
