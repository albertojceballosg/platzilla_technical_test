<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/webmail/lib/WebmailUtils.class.php');

	global $adb, $current_user, $site_URL, $webMailClient;

	$accessTokenData              = PlatzillaUtils::purify ($_POST, 'accesstoken');
	$emailAddress                 = PlatzillaUtils::purify ($_POST, 'emailaddress');
	$plainPassword                = PlatzillaUtils::purify ($_POST, 'password', null);
	$incomingFolderName           = PlatzillaUtils::purify ($_POST, 'incomingfoldername');
	$outgoingFolderName           = PlatzillaUtils::purify ($_POST, 'outgoingfoldername');
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
	$returnAction                 = PlatzillaUtils::purify ($_POST, 'return_action', 'AccountListView');
	$returnModule                 = PlatzillaUtils::purify ($_POST, 'return_module', 'webmail');

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
		$accountData  = array (
			'accesstokendata'    => $accessTokenData,
			'emailaddress'       => $emailAddress,
			'incomingfoldername' => $incomingFolderName,
			'outgoingfoldername' => $outgoingFolderName,
			'plainpassword'      => $plainPassword,
			'provider'           => $providerData,
		);
		$isInstance    = !empty ($_SESSION ['platInstancia']);
		$dummy         = parse_url ($site_URL, PHP_URL_HOST);
		$localHostName = !empty ($dummy) ? $dummy : 'localhost';
		$domain        = strtolower (substr ($emailAddress, (strpos ($emailAddress, '@') + 1)));
		WebmailUtils::saveMailAccount ($adb, $accountData, $webMailClient ['encryptionkey'], $current_user->id, $localHostName, $isInstance);
		WebmailUtils::saveMailProvider ($adb, $domain, $providerData);

		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => "Se ha asociado la cuenta de correo: {$emailAddress}",
		);
		header ("Location: index.php?module={$returnModule}&action={$returnAction}");
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
		header ("Location: index.php?module=webmail&action=AccountEditView&return_module={$returnModule}&return_action={$returnAction}");
	}
	exit ();
