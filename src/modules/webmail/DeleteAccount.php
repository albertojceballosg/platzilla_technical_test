<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/webmail/lib/WebmailUtils.class.php');

	global $adb, $current_user;

	$emailAddress = PlatzillaUtils::purify ($_POST, 'emailaddress');

	WebmailUtils::deleteMailAccount ($adb, $emailAddress, $current_user->id);
	$_SESSION ['flashmessage'] = array (
		'iserror' => false,
		'message' => "Se ha eliminado la cuenta de correo {$emailAddress}",
	);
	header ('Location: index.php?module=webmail&action=AccountListView&parenttab=Settings');
	exit ();