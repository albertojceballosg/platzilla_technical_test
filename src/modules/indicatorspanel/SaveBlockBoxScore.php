<?php
	require_once ('modules/indicatorspanel/indicatorspanel.php');
	// Agregado por EB para integrar BUGSNAG - 20200527
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
	// Agregado por EB para integrar BUGSNAG - 20200527

	global $adb;

	$mode = PlatzillaUtils::purify ($_REQUEST, 'mode');

	$monthSearch = PlatzillaUtils::purify ($_REQUEST, 'monthsearch', date ('m'));
	$recordId    = PlatzillaUtils::purify ($_REQUEST, 'record');
	$from        = PlatzillaUtils::purify ($_REQUEST, 'date_from');
	$to          = PlatzillaUtils::purify ($_REQUEST, 'date_to');
	$type        = PlatzillaUtils::purify ($_REQUEST, 'type');

	$boxScore = IndicatorsPanel::getInstance ($adb, $monthSearch, $recordId, $from, $to);

	$blockId = 0;
	if ($mode != 'delete') {
		if (isset($_REQUEST['colorbase'])) {
			$baseColor = $_REQUEST['colorbase'];
		} else {
			$baseColor = null;
		}

		if (isset($_REQUEST['colordegrade'])) {
			$degradeeColor = $_REQUEST['colordegrade'];
		} else {
			$degradeeColor = null;
		}
		$isInstance = !empty ($_SESSION ['platInstancia']) ? 1 : 0;
		$blockId    = $boxScore->saveBlock ($baseColor, $degradeeColor, $recordId, $isInstance, $type);
	} else {
		$blockId = $boxScore->deleteBlock ($type);
	}

	if ($blockId > 0) {
		echo 'success';
	} else {
		echo "error - {$blockId}";
	}
