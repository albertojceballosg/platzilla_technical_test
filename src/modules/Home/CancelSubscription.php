<?php
	require_once ('config.inc.php');
	require_once ('include/platzilla/Managers/PlatformManager.php');
	// Agregado por EB para integrar BUGSNAG - 20200328
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
	// Agregado por EB para integrar BUGSNAG - 20200328

	global $dbconfig;

	$instanceCode = $_SESSION ['platInstancia'];
	try {
		if (empty ($instanceCode)) {
			throw new Exception ('No tienes una instancia válida');
		}

		$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
		PlatformManager::getInstance ($masterAdb, $dbconfig ['db_serverForNewUsers'])->deleteInstance ($instanceCode);
		session_destroy ();
		header ('Location: subscription-cancelled.php');
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
		header ("Location: index.php?module=Home&action=CustomerView");
	}
	exit ();
