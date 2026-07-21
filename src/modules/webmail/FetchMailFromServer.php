<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/webmail/lib/WebmailUtils.class.php');

	global $adb, $current_user, $upload_badext, $webMailClient;

	try {
		$isInstance = !empty ($_SESSION ['platInstancia']);
		$accounts   = WebmailUtils::fetchMailAccounts ($adb, $current_user->id, $isInstance);
		if (!count ($accounts)) {
			throw new Exception ('No se han encontrado cuentas de correo asociadas');
		}

		$results = array ();
		foreach ($accounts as $account) {
			$mask = ($isInstance && ($account->getEmailAddress () == WebmailUtils::DEFAULT_MAIL)) ? $current_user->column_fields ['email1'] : null;
			try {
				$mailMessages = WebmailUtils::fetchMailMessages ($adb, $account, $current_user->id, $webMailClient ['encryptionkey'], $mask);
				if ((empty ($mailMessages ['incoming'])) && (empty ($mailMessages ['outgoing']))) {
					$results [] = "No hay nuevos correos de la cuenta {$account->getEmailAddress ()}";
				} else {
					if (!empty ($mailMessages ['incoming'])) {
						WebmailUtils::saveMailMessages ($adb, $account->getEmailAddress (), $mailMessages ['incoming'], WebmailUtils::TYPE_INCOMING, $current_user->id, $upload_badext);
					}
					if (!empty ($mailMessages ['outgoing'])) {
						WebmailUtils::saveMailMessages ($adb, $account->getEmailAddress (), $mailMessages ['outgoing'], WebmailUtils::TYPE_OUTGOING, $current_user->id, $upload_badext);
					}
					$results [] = "Se han obtenido los nuevos correos de la cuenta {$account->getEmailAddress ()}";
				}
			} catch (Exception $e) {
				$results [] = "Se ha presentado un error al obtener los correos de la cuenta {$account->getEmailAddress ()}: {$e->getMessage ()}";
			}
		}

		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => join ('<br />', $results),
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	header ('Location: index.php?module=Home&action=MessagesListView');
	exit ();
