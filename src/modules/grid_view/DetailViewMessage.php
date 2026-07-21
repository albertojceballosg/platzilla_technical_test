<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/notification_center/lib/NotificationHelper.class.php');
	require_once ('modules/webmail/lib/WebmailUtils.class.php');

	global $adb, $app_strings, $current_user, $mod_strings, $theme, $site_URL;

	setBugSnag ($site_URL);

	$moduleName = PlatzillaUtils::purify ($_GET, 'formodule');
	$record     = PlatzillaUtils::purify ($_GET, 'record');

	try {
		if (empty ($moduleName)) {
			throw new Exception ('Módulo no recibido!');
		}

		if (empty ($record)) {
			throw new Exception ('Numero de registro no recibido');
		}

		$searchParameter              = NotificationHelper::getInitialParameters ();
		$searchParameter ['recordId'] = $record;
		$searchParameter ['module']   = $moduleName;
		$parleyArray                  = NotificationHelper::searchParleyByWhere ($adb, $current_user, $searchParameter);
		$activeUserToChat             = NotificationHelper::fetchActiveUserByRecord ($adb, $record);
		$relatedUserToChat            = NotificationHelper::fetchRelatedUserByRecord ($adb, $record, $moduleName);
		$relatedUserToChat []         = $record;
		$totalActiveUser              = count ($activeUserToChat);
		$activeUserToChat             = array_diff ($activeUserToChat, array ($current_user->id));
		$relatedUseresInChat          = array_values (array_unique ($activeUserToChat));
		$keysActiveUsers              = array_keys ($activeUserToChat);
		$lastUserToChat               = (!empty ($activeUserToChat)) ? $activeUserToChat [ $keysActiveUsers [0] ] : 0;
		$activeUserToChat             = array_unique (array_merge ($activeUserToChat, $relatedUserToChat));

		$relatedEmailsData = WebmailUtils::fetchRelatedEmailsData ($adb, $record);

		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('ACTIVE_USERS_CHATS', $activeUserToChat);
		$smarty->assign ('CHATS', $parleyArray);
		$smarty->assign ('CURRENT_USER_ID', $current_user->id);
		$smarty->assign ('CURRENT_USER_NAME', "{$current_user->column_fields ['first_name']} {$current_user->column_fields ['last_name']}");
		$smarty->assign ('ID', $record);
		$smarty->assign ('LAST_USERS_CHATS', $lastUserToChat);
		$smarty->assign ('MODULE', $moduleName);
		$smarty->assign ('MODCHAT', return_module_language ($current_language, 'notification_center'));
		$smarty->assign ('RECORD', $record);
		$smarty->assign ('RELATED_EMAILS_DATA', $relatedEmailsData);
		$smarty->assign ('TOTAL_ACTIVE_USERS_CHATS', $totalActiveUser);
		$smarty->assign ('RELATED_USERS_CHAT', join (',', $relatedUseresInChat));
		$smarty->assign ('SEARCH_USERS_CHATS', json_encode (NotificationHelper::fetchUserToChat ($adb, $_SESSION ['plat'])));
		$smarty->assign ('USERS_CHATS', NotificationHelper::fetchUserToChat ($adb, $_SESSION ['plat']));
	} catch (Exception $e) {
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('IS_ERROR', true);
	}
	$smarty->display ('modules/grid_view/BoxContenets/DetailViewMessage.tpl');
