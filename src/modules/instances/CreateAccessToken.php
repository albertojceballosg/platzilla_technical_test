<?php
	require_once ('include/platzilla/Managers/PlatformManager.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
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

	global $adb, $dbconfig, $current_user;

	try {
		if ((!empty ($_SESSION ['platInstancia'])) || (!is_admin ($current_user))) {
			throw new Exception ('Acceso denegado');
		}

		$instanceCode           = PlatzillaUtils::purify ($_POST, 'code');
		$masterAdb              = AdbManager::getInstance ()->getMasterAdb ();
		$temporaryAdministrator = PlatformManager::getInstance ($masterAdb, $dbconfig ['db_serverForNewUsers'])->createInstanceTemporaryAdmin ($instanceCode);
		$platzillaRootUri       = PlatzillaUtils::getPlatzillaRootUri ();
		$token                  = sha1 ("{$instanceCode}-{$temporaryAdministrator->getUserName ()}");
		$data                   = array ('url' => "{$platzillaRootUri}/index.php?module=Users&action=Login&impersonationtoken={$token}");
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json');
		echo json_encode ($data);
	} catch (Exception $e) {
		header ('HTTP/1.1 400 Bad request');
		header ('Content-Type: application/json');
		echo json_encode ($e->getMessage ());
	}
	exit ();
