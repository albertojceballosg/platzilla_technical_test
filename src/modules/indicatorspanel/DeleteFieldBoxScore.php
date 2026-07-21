<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/indicatorspanel/lib/IndicatorsPanelHelper.class.php');
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

	$recordId = PlatzillaUtils::purify ($_REQUEST, 'recordop');

	if ((!empty ($recordId))) {
		$row    = IndicatorsPanelHelper::getCalcIdRel ($adb, $recordId);
		$result = $adb->pquery ('DELETE FROM vtiger_boxscore_operation WHERE operation_id IN (?,?)', array ($recordId, $row['operation_id']));
		if ($result) {
			echo 'delete_on';
		} else {
			echo 'delete_off';
		}
	} else {
		echo 'delete_off';
	}
