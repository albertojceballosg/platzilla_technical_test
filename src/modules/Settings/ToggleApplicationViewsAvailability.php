<?php
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb;

	$returnAction = PlatzillaUtils::purify ($_GET, 'returnaction');
	$returnModule = PlatzillaUtils::purify ($_GET, 'returnmodule');
	$returnRecord = PlatzillaUtils::purify ($_GET, 'returnrecord');

	try {
		PlatformUtils::toggleApplicationViewsAvailability ($adb);
		$_SESSION ['flashmessage'] = array (
			'IS_ERROR' => false,
			'MESSAGE'  => 'Se han activado las vistas de aplicaciones',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'IS_ERROR' => true,
			'MESSAGE'  => "Se ha presentado un error: {$e->getMessage ()}",
		);
	}
	$recordUriPart = !empty ($returnRecord) ? "&record={$returnRecord}" : '';
	header ("Location: index.php?module={$returnModule}&action={$returnAction}{$recordUriPart}");
	exit ();
