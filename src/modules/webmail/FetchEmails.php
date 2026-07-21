<?php
	require_once ('include/MailManager/PlatzillaMailManager.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/webmail/lib/WebmailUtils.class.php');

	global $adb, $current_user, $webMailClient;

	use Platzilla\MailManager\Service\MailManager;

	$providerName = PlatzillaUtils::purify ($_GET, 'providername');

	try {
		if (empty ($providerName)) {
			throw new Exception ('No se ha suministrado la cuenta de correo');
		}

		$account = WebmailUtils::fetchMailAccount ($adb, $current_user->id);

		$mm = new MailManager ();
		$mm->loginAccount ($account, $webMailClient ['encryptionkey']);
		$mm->logout ();

//		$_SESSION ['flashmessage'] = array (
//			'iserror' => false,
//			'message' => 'Se han obtenido los correos',
//		);
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => 'Funcionalidad pendiente',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	header ('Location: index.php?module=Home&action=index');
	exit ();
