<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/webmail/lib/WebmailUtils.class.php');

	use League\OAuth2\Client\Token\AccessToken;
	use Platzilla\MailManager\Utils\MailUtils;

	global $webMailClient;

	$emailAddress                 = PlatzillaUtils::purify ($_POST, 'emailaddress');
	$incomingAuthenticationMethod = PlatzillaUtils::purify ($_POST, 'incomingauthenticationmethod');
	$incomingHostName             = PlatzillaUtils::purify ($_POST, 'incominghostname');
	$incomingPort                 = PlatzillaUtils::purify ($_POST, 'incomingport');
	$incomingSecurityType         = PlatzillaUtils::purify ($_POST, 'incomingsecuritytype');
	$incomingService              = PlatzillaUtils::purify ($_POST, 'incomingservice');
	$incomingUserNameType         = PlatzillaUtils::purify ($_POST, 'incomingusernametype');
	$outgoingAuthenticationMethod = PlatzillaUtils::purify ($_POST, 'outgoingauthenticationmethod');
	$outgoingHostName             = PlatzillaUtils::purify ($_POST, 'outgoinghostname');
	$outgoingPort                 = PlatzillaUtils::purify ($_POST, 'outgoingport');
	$outgoingSecurityType         = PlatzillaUtils::purify ($_POST, 'outgoingsecuritytype');
	$outgoingService              = PlatzillaUtils::purify ($_POST, 'outgoingservice');
	$outgoingUserNameType         = PlatzillaUtils::purify ($_POST, 'outgoingusernametype');

	try {
		$password    = !empty ($plainPassword) ? MailUtils::encrypt ($plainPassword, $webMailClient ['encryptionkey']) : null;
		$accessToken = !empty ($accessTokenData) ? new AccessToken ($accessTokenData) : null;

		$accountData = array (
			'accessToken'        => !empty ($accessTokenData) ? new AccessToken ($accessTokenData) : null,
			'emailAddress'       => $emailAddress,
			'incomingFolderName' => null,
			'outgoingFolderName' => null,
			'password'           => !empty ($plainPassword) ? MailUtils::encrypt ($plainPassword, $webMailClient ['encryptionkey']) : null,
			'provider'           => array (
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
			),
		);

		$_SESSION ['oauth2mailaccount'] = json_encode ($accountData);

		$statusCode    = 200;
		$statusMessage = 'OK';
		$data          = 'OK';
	} catch (\Exception $e) {
		$statusCode    = 400;
		$statusMessage = 'Bad request';
		$data          = $e->getMessage ();
	}
	header ("HTTP/1.1 {$statusCode} {$statusMessage}");
	header ('Content-Type: application/json');
	echo json_encode ($data);
	exit ();
