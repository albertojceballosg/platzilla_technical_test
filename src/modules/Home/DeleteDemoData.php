<?php
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

	$moduleName = PlatzillaUtils::purify ($_POST, 'formodule');

	try {
		if (!empty ($moduleName)) {
			$whereClause = 'AND setype=?';
			$arguments = array ($moduleName);
		} else {
			$whereClause = '';
			$arguments   = array ();
		}
		$adb->pquery ("DELETE FROM vtiger_crmentity WHERE demo=1 {$whereClause}", $arguments);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'Se han eliminado todos los datos de prueba',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	if (!empty ($moduleName)) {
		header ("Location: index.php?module={$moduleName}&action=ListView");
	} else {
		header ('Location: index.php');
	}
	exit ();
