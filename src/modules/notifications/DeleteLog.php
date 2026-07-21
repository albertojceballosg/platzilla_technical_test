<?php
	require_once ('Smarty_setup.php');
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

	global $adb, $current_user;

	$platform = $_SESSION ['plat'];
	try {
		$recordId = PlatzillaUtils::purify ($_POST, 'record');
		if (empty ($recordId)) {
			throw new Exception ('No has suministrado el ID de la notificación');
		}

		$notification = NotificationUtils::fetchNotification ($adb, $recordId);
		if (empty ($notification)) {
			throw new Exception ('No se encuentra registrada la notificación con el ID suministrado');
		}

		$platzillaRootFolderPath = PlatzillaUtils::getPlatzillaRootFolderPath ();
		$notify                  = "notify_{$notification->getId ()}.log";
		$logFilePath             = "{$platzillaRootFolderPath}/{$platform}/logs/notificationslogs/{$notify}";
		if (file_exists ($logFilePath)) {
			unlink ($logFilePath);
		}

		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'Se ha eliminado el registro de eventos',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	header ('Location: index.php?module=notifications&action=ListView&parenttab=Settings');
	exit ();
