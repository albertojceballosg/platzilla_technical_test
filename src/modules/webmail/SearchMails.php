<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/webmail/lib/WebmailUtils.class.php');

	global $adb, $current_user;

	$from        = PlatzillaUtils::purify ($_GET, 'from', date_format (date_sub (date_create (), date_interval_create_from_date_string ('7 days')), 'Y-m-d'));
	$status      = PlatzillaUtils::purify ($_GET, 'status', WebmailUtils::STATUS_ALL);
	$to          = PlatzillaUtils::purify ($_GET, 'to', date_format (date_create (), 'Y-m-d'));
	$emailPeriod = PlatzillaUtils::purify ($_GET,'emailsperiod', null);

	try {
		$filters    = array ('from' => $from, 'to' => $to, 'status' => $status);
		$emailsData = WebmailUtils::fetchEmailsData ($adb, $current_user->id, $filters, false);

		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('FROM', $from);
		$smarty->assign ('TO', $to);
		$smarty->assign ('PERIOD', $emailPeriod);
		if (!empty ($emailsData)) {
			$availableModules = ModuleManager::getInstance ($adb)->fetchModulesByType (Module::TYPE_USER, true, !empty ($_SESSION ['platInstancia']));
			usort ($availableModules, function (Module $moduleA, Module $moduleB) {
				return strcmp ($moduleA->getLabel (), $moduleB->getLabel ());
			});
			$user = UserManager::getInstance ($adb, $_SESSION ['plat'])->fetchUserById ($current_user->id, true);
			$smarty->assign ('AVATAR_URI', $user->getImageUri ());

			$messagesData = array ();
			foreach ($emailsData as $emailData) {
				$smarty->assign ('ACCOUNT_NAME', $emailData ['account']);
				$smarty->assign ('AVAILABLE_MODULES', $availableModules);
				$smarty->assign ('IS_EMAIL', true);
				$smarty->assign ('MESSAGE_ID', $emailData ['crmid']);
				$smarty->assign ('REGISTERED_AS', $emailData ['registeredas']);
				$smarty->assign ('RELATED_ENTITIES_DATA', $emailData ['relatedentities']);
				$smarty->assign ('SENDER', $emailData ['sender']);
				$smarty->assign ('SINCE', $emailData ['timesince']);
				$smarty->assign ('STATUS_EMAIL', (key_exists ('status_email', $emailData)) ? $emailData ['status_email'] : null);
				$smarty->assign ('SUBJECT', $emailData ['subject']);
				if ($emailData ['type'] == WebmailUtils::TYPE_INCOMING) {
					$messagesData [] = $smarty->fetch ('Home/TabsContents/MessageReceived.tpl');
				} else {
					$messagesData [] = $smarty->fetch ('Home/TabsContents/MessageSent.tpl');
				}
			}
			$data = join ('', $messagesData);
		} else {
			$smarty->assign ('SENDER', 'Platzilla');
			$smarty->assign ('SUBJECT', 'No se encontraron correos en el período seleccionado');
			$data = $smarty->fetch ('Home/TabsContents/NoMessagesFound.tpl');
		}
		$statusCode = 200;
	} catch (Exception $e) {
		$statusCode = 400;
		$data       = $e->getMessage ();
	}
	header ("HTTP/1.1 {$statusCode} {$statusMessage}");
	header ('Content-Type: text/html');
	echo $data;
	exit ();
