<?php
	require_once ('include/platzilla/Managers/PlatformManager.php');
	require_once ('include/utils/AdbManager.class.php');
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

	global $current_user, $dbconfig;

	$instanceCode = PlatzillaUtils::purify ($_POST, 'code');
	$page         = PlatzillaUtils::purify ($_POST, 'page');
	$instanceType = PlatzillaUtils::purify ($_POST, 'listType');

	$rowsPerPage = 25;
	try {
		if ((!empty ($_SESSION ['platInstancia'])) || (!is_admin ($current_user))) {
			throw new Exception ('Acceso denegado');
		} else if (empty ($instanceCode)) {
			throw new Exception ('No se ha suministrado el código de la instancia');
		}

		$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
		PlatformManager::getInstance ($masterAdb, $dbconfig ['db_serverForNewUsers'])->deleteInstance ($instanceCode);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => "Se ha eliminado la instancia {$instanceCode}",
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	header ("Location: index.php?module=instances&action=index&page={$page}&listType={$instanceType}");
	exit ();
