<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/webmail/lib/WebmailUtils.class.php');

	global $adb, $current_user;

	$from = PlatzillaUtils::purify ($_GET, 'from', date_format (date_sub (date_create (), date_interval_create_from_date_string ('7 days')), 'Y-m-d'));
	$to   = PlatzillaUtils::purify ($_GET, 'to', date_format (date_create (), 'Y-m-d'));

	try {
		$filters             = array ('from' => $from, 'to' => $to);
		$unrelatedEmailsData = WebmailUtils::fetchUnrelatedEmailsData ($adb, $current_user->id, $filters, false);
		$statusCode          = 200;
		$statusMessage       = 'OK';
		$data                = !empty ($unrelatedEmailsData) ? $unrelatedEmailsData : array ();
	} catch (Exception $e) {
		$statusCode    = 400;
		$statusMessage = 'Bad request';
		$data          = $e->getMessage ();
	}
	header ("HTTP/1.1 {$statusCode} {$statusMessage}");
	header ('Content-Type: application/json');
	echo json_encode ($data);
	exit ();
