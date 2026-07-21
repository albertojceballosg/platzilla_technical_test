<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/webmail/lib/WebmailUtils.class.php');

	global $adb, $current_user;

	$record           = PlatzillaUtils::purify ($_POST, 'record');
	$relatedEntityIds = PlatzillaUtils::purify ($_POST, 'relatedentityids');

	try {

		WebmailUtils::relateEmail ($adb, $record, $relatedEntityIds);

		$statusCode    = 200;
		$statusMessage = 'OK';
		$data          = 'OK';
	} catch (Exception $e) {
		$statusCode    = 400;
		$statusMessage = 'Bad request';
		$data          = $e->getMessage ();
	}
	header ("HTTP/1.1 {$statusCode} {$statusMessage}");
	header ('Content-Type: application/json');
	echo json_encode ($data);
	exit ();
