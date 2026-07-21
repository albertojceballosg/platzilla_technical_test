<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/webmail/lib/WebmailUtils.class.php');

	global $adb, $current_user, $site_URL, $webMailClient;

	$bcc     = PlatzillaUtils::purify ($_POST, 'bcc');
	$body    = PlatzillaUtils::purify ($_POST, 'body');
	$cc      = PlatzillaUtils::purify ($_POST, 'cc');
	$from    = PlatzillaUtils::purify ($_POST, 'from');
	$subject = PlatzillaUtils::purify ($_POST, 'subject');
	$to      = PlatzillaUtils::purify ($_POST, 'to');

	try {
		$dummy         = parse_url ($site_URL, PHP_URL_HOST);
		$localHostName = !empty ($dummy) ? $dummy : 'localhost';
		$emailData     = array (
			'bcc'     => $bcc,
			'body'    => $body,
			'cc'      => $cc,
			'from'    => $from,
			'subject' => $subject,
			'to'      => $to,
		);
		WebmailUtils::sendEmailData ($adb, $from, $emailData, $current_user->id, $webMailClient ['encryptionkey'], $localHostName);

		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json');
		echo json_encode ('OK');
	} catch (Exception $e) {
		$statusCode    = !empty ($e->getCode ()) ? $e->getCode () : 500;
		$statusMessage = !empty ($e->getCode ()) ? 'Bad request' : 'Internal server error';
		header ('HTTP/1.1 400 Bad request');
		header ('Content-Type: application/json');
		echo json_encode ($e->getMessage ());
	}
	exit ();
