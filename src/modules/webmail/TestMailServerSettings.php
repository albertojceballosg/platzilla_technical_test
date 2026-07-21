<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/webmail/lib/WebmailUtils.class.php');

	global $adb;

	$incomingAuthenticationMethod = PlatzillaUtils::purify ($_GET, 'incomingauthenticationmethod');
	$incomingHostName             = PlatzillaUtils::purify ($_GET, 'incominghostname');
	$incomingPort                 = PlatzillaUtils::purify ($_GET, 'incomingport');
	$incomingSecurityType         = PlatzillaUtils::purify ($_GET, 'incomingsecuritytype');
	$incomingService              = PlatzillaUtils::purify ($_GET, 'incomingservice');
	$incomingUserNameType         = PlatzillaUtils::purify ($_GET, 'incomingusernametype');
	$outgoingAuthenticationMethod = PlatzillaUtils::purify ($_GET, 'outgoingauthenticationmethod');
	$outgoingHostName             = PlatzillaUtils::purify ($_GET, 'outgoinghostname');
	$outgoingPort                 = PlatzillaUtils::purify ($_GET, 'outgoingport');
	$outgoingSecurityType         = PlatzillaUtils::purify ($_GET, 'outgoingsecuritytype');
	$outgoingService              = PlatzillaUtils::purify ($_GET, 'outgoingservice');
	$outgoingUserNameType         = PlatzillaUtils::purify ($_GET, 'outgoingusernametype');

	try {
		$providerData = array (
			'incomingauthenticationmethod' => $incomingAuthenticationMethod,
			'incominghostname'             => $incomingHostName,
			'incomingport'                 => $incomingPort,
			'incomingsecuritytype'         => $incomingSecurityType,
			'incomingservice'              => $incomingService,
			'incomingusernametype'         => $incomingUserNameType,
			'outgoingauthenticationmethod' => $outgoingAuthenticationMethod,
			'outgoinghostname'             => $outgoingHostName,
			'outgoingport'                 => $outgoingPort,
			'outgoingsecuritytype'         => $outgoingSecurityType,
			'outgoingservice'              => $outgoingService,
			'outgoingusernametype'         => $outgoingUserNameType,
		);
		$isValid = WebmailUtils::testMailProvider ($providerData);
		if (!$isValid) {
			throw new Exception ('Los datos suministrados no son válidos');
		}

		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json');
		echo json_encode ('OK');
	} catch (Exception $e) {
		header ("HTTP/1.1 400 Bad request");
		header ('Content-Type: application/json');
		echo json_encode ($e->getMessage ());
	}
	exit ();
