<?php
	require_once ('include/platzilla/Managers/PlatformManager.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
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

	global $adb;

	$moduleName = PlatzillaUtils::purify ($_POST, 'fld_module');
	$urlReturn  = PlatzillaUtils::purify ($_POST, 'urlReturn');

	$urlReturn = str_replace ('@', '&', $urlReturn);
	$isClear   = true;

	try {
		if (!empty($_SESSION ['platInstancia'])) {
			$instanceCode = $_SESSION ['platInstancia'];
			$masterAdb    = AdbManager::getInstance ()->getMasterAdb ();
			$targetAdb    = AdbManager::getInstance ()->getTargetInstanceAdb ($instanceCode);
			$result       = ExampleDataManager::deleteData ($targetAdb);
			PlatformManager::getInstance ($masterAdb)->updateInstancePattern ($instanceCode, false);
		} else {
			$result = true;
		}

		$_SESSION ['flashmessage'] = array (
			'iserror' => !$result,
			'message' => $result ? 'Todos los datos de prueba han sido eliminados!' : 'Imposible borrar datos de prueba!',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	header ('Location: index.php?' . $urlReturn);
	exit ();
