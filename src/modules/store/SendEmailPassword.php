<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/webmail/lib/WebmailUtils.class.php');


	global $adb, $current_user, $site_URL, $webMailClient;

	$to        = PlatzillaUtils::purify ($_POST, 'usuarioEmail');
	$ebook     = PlatzillaUtils::purify ($_POST, 'ebookName');
	$passWoord = PlatzillaUtils::purify ($_POST, 'codeToken');

	try {
		$dummy         = parse_url ($site_URL, PHP_URL_HOST);
		$localHostName = !empty ($dummy) ? $dummy : 'localhost';
		$from          = 'notificaciones@platzilla.com';
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('EBOOK',$ebook);
		$smarty->assign ('EMAIL',$to);
		$smarty->assign ('PASSWORD', base64_decode ($passWoord));
		$body = $smarty->fetch('modules/store/PasswordEmailBody.tpl');
		$emailData     = array (
			'body'    => $body,
			'from'    => $from,
			'subject' => '[Platzilla] Información de acceso a tu cuenta',
			'to'      => $to,
		);

		WebmailUtils::sendEmailData ($adb, $from, $emailData, 1, $webMailClient ['encryptionkey'], $localHostName);


		header('Access-Control-Allow-Origin: *');
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json; charset=utf-8');
		echo json_encode (array ('error' => 'OK'));
	} catch (Exception $e) {
		header('Access-Control-Allow-Origin: *');
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json; charset=utf-8');
		echo json_encode (array('error' => $e->getMessage ()));
	}
	exit ();
