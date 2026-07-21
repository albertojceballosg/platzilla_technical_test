<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/webmail/lib/WebmailUtils.class.php');

	global $adb;

	$record = PlatzillaUtils::purify ($_GET, 'record');

	$emailData = WebmailUtils::fetchEmailData ($adb, $record);
	if (empty ($emailData)) {
		echo '';
	} else {
		echo $emailData ['body'];
	}
	exit ();