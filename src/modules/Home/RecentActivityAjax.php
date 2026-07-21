<?php
	require_once ('modules/Home/lib/HomeUtils.class.php');
	// Agregado por EB para integrar BUGSNAG - 20200330
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
	// Agregado por EB para integrar BUGSNAG - 20200330

	global $adb, $current_user;

	$lastweek           = date ('Y-m-d', mktime (0, 0, 0, date ('m'), (date ('d') - 7), date ('Y')));
	$startDateTime      = "{$lastweek} 00:00:00";
	$endDateTime        = date ('Y-m-d') . ' 23:59:00';
	$recentActivity = HomeUtils::getAllActivity ($adb, $current_user, $startDateTime, $endDateTime);
	if (!empty ($recentActivity)) {
		echo json_encode ($recentActivity);
	}
	exit ();