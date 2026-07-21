<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/webmail/lib/WebmailUtils.class.php');

	global $adb;

	$accessToken          = PlatzillaUtils::purify ($_GET, 'accesstoken');
	$authenticationMethod = PlatzillaUtils::purify ($_GET, 'authenticationmethod');
	$emailAddress         = PlatzillaUtils::purify ($_GET, 'emailaddress');
	$hostName             = PlatzillaUtils::purify ($_GET, 'hostname');
	$port                 = PlatzillaUtils::purify ($_GET, 'port');
	$securityType         = PlatzillaUtils::purify ($_GET, 'securitytype');
	$service              = PlatzillaUtils::purify ($_GET, 'service');
	$userNameType         = PlatzillaUtils::purify ($_GET, 'usernametype');

	try {
		$providerData = array (
			'incomingauthenticationmethod' => $authenticationMethod,
			'incominghostname'             => $hostName,
			'incomingport'                 => $port,
			'incomingsecuritytype'         => $securityType,
			'incomingservice'              => $service,
			'incomingusernametype'         => $userNameType,
		);

		$folders = WebmailUtils::getMailSubscribedFolders ($emailAddress, base64_decode ($accessToken), $providerData);

		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json');
		echo json_encode ($folders);
	} catch (Exception $e) {
		header ('HTTP/1.1 400 Bad Request');
		header ('Content-Type: application/json');
		echo json_encode ($e->getMessage ());
	}
	exit ();
