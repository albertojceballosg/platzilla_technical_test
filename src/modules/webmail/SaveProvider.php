<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/webmail/lib/WebmailUtils.class.php');

	global $adb, $current_user;

	$name = PlatzillaUtils::purify ($_POST, 'name');

	try {
		$provider = array (
			'label'                        => PlatzillaUtils::purify ($_POST, 'label'),
			'name'                         => $name,
			'incomingauthenticationmethod' => PlatzillaUtils::purify ($_POST, 'incomingauthenticationmethod'),
			'incominghostname'             => PlatzillaUtils::purify ($_POST, 'incominghostname'),
			'incomingport'                 => PlatzillaUtils::purify ($_POST, 'incomingport'),
			'incomingprotocol'             => PlatzillaUtils::purify ($_POST, 'incomingprotocol'),
			'incomingsecuritytype'         => PlatzillaUtils::purify ($_POST, 'incomingsecuritytype'),
			'outgoingauthenticationmethod' => PlatzillaUtils::purify ($_POST, 'outgoingauthenticationmethod'),
			'outgoinghostname'             => PlatzillaUtils::purify ($_POST, 'outgoinghostname'),
			'outgoingport'                 => PlatzillaUtils::purify ($_POST, 'outgoingport'),
			'outgoingprotocol'             => PlatzillaUtils::purify ($_POST, 'outgoingprotocol'),
			'outgoingsecuritytype'         => PlatzillaUtils::purify ($_POST, 'outgoingsecuritytype'),
		);
		WebmailUtils::saveEmailProvider ($adb, $provider);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'Se ha guardado el proveedor',
		);
		header ('Location: index.php?module=webmail&action=ProviderListView');
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
			'data'    => $_POST,
		);
		$nameUrlPart = !empty ($name) ? "&name={$name}" : '';
		header ("Location: index.php?module=webmail&action=ProviderEditView{$nameUrlPart}");
	}
	exit ();
