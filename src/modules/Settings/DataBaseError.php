<?php
	require_once ('Smarty_setup.php');
	require_once ('modules/webmail/lib/WebmailUtils.class.php');
	require_once ('include/utils/AdbManager.class.php');

	function sendMessaje ($body) {
		global $theme, $site_URL, $webMailClient;
		$masterAdb     = AdbManager::getInstance ()->getMasterAdb ();
		$dummy         = parse_url ($site_URL, PHP_URL_HOST);
		$localHostName = !empty ($dummy) ? $dummy : 'localhost';
		$emailData     = array (
			'bcc'     => '',
			'body'    => $body,
			'cc'      => '',
			'from'    => 'notificaciones@platzilla.com',
			'subject' => 'Error en base de datos',
			'to'      => 'incidencias@platzilla.com',
		);
		WebmailUtils::sendEmailData ($masterAdb, 'notificaciones@platzilla.com', $emailData, 1, $webMailClient ['encryptionkey'], $localHostName);
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('THEME', $theme);
		echo $smarty->fetch ('Settings/DataBaseError.tpl');
	};
