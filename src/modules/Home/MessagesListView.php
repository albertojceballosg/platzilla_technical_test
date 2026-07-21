<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Data/ApplicationsManager.php');
	require_once ('include/platzilla/Data/BoxScoreManager.php');
	require_once ('include/platzilla/Data/GraphicManager.php');
	require_once ('include/platzilla/Managers/PlatformSubscriptionManager.php');
	require_once ('include/platzilla/Managers/UserManager.php');
	require_once ('include/platzilla/Utils/JSGraphicUtils.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/DataViewUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Courses/lib/CoursesHelper.php');
	require_once ('modules/Home/lib/HomeUtils.class.php');
	require_once ('modules/News/lib/NewsUtils.php');
	require_once ('modules/notification_center/lib/NotificationHelper.class.php');
	require_once ('modules/notifications/lib/NotificationUtils.class.php');
	require_once ('modules/notifications/lib/NotificationPeriodUtils.class.php');
	require_once ('modules/operating_modes/lib/OperatingModesHelper.class.php');
	require_once ('modules/panelusuarios/lib/UsersHelper.class.php');
	require_once ('modules/store/lib/StoreUtils.class.php');
	require_once ('modules/webmail/lib/WebmailUtils.class.php');
	
	global $adb, $app_strings, $current_language, $current_user, $currentModule, $platPrincipal, $webMailClient, $theme, $site_URL;
	
	setBugSnag ($site_URL);
	
	$selectedTab = PlatzillaUtils::purify ($_GET, 'tab', null);
	
	try {
		$masterAdb  = AdbManager::getInstance ()->getMasterAdb ();
		$isInstance = !empty ($_SESSION ['platInstancia']);
		if ($isInstance) {
			if (!StoreUtils::isInstanceVerified ($_SESSION ['platInstancia'])) {
				throw new Exception ('Debes verificar tu cuenta', 400);
			}
			$psm          = PlatformSubscriptionManager::getInstance ($masterAdb);
			$subscription = $psm->fetchSubscription ($_SESSION ['platInstancia']);
			if ((empty ($subscription)) || ($subscription->getStatus () == PlatformSubscription::STATUS_INACTIVE)) {
				throw new Exception ('Tu suscripción se encuentra inactiva', 403);
			}
			
			$canCreateRecords = true;
		} else {
			$canCreateRecords = true;
		}
		$smarty           = new vtigerCRM_Smarty ();
		$availableModules = ModuleManager::getInstance ($adb)->fetchModulesByType (Module::TYPE_USER, true, $isInstance);
		usort (
			$availableModules,
			function (Module $moduleA, Module $moduleB) {
				return strcmp ($moduleA->getLabel (), $moduleB->getLabel ());
			}
		);
		$mailAccounts = WebmailUtils::fetchMailAccounts ($adb, $current_user->id, $isInstance);
		
		$filters = array (
			'from'   => date_format (date_sub (date_create (), date_interval_create_from_date_string ('7 days')), 'Y-m-d'),
			'status' => WebmailUtils::STATUS_ALL,
			'to'     => date_format (date_create (), 'Y-m-d'),
		);
		$emailsData  = WebmailUtils::fetchEmailsData ($adb, $current_user->id, $filters);
		$totalUnread = WebmailUtils::getMailCountByStatus ($adb, $current_user->id);
		$smarty           = new vtigerCRM_Smarty ();
		// Comunes a todos los tabs
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('CAN_CREATE_RECORDS', $canCreateRecords);
		$smarty->assign ('DEFAULT_OPERATING', $current_user->defaultOperating);
		$smarty->assign ('IS_ADMIN', is_admin ($current_user));
		$smarty->assign ('IS_INSTANCE', !empty ($_SESSION ['platInstancia']));
		$smarty->assign ('ROOT_FOLDER_PATH', PlatzillaUtils::getPlatzillaRootFolderPath ());
		$smarty->assign ('IS_MOTHER', empty ($_SESSION ['platInstancia']));
		$smarty->assign ('SELECTED_TAB', $selectedTab);
		$smarty->assign ('TAB_GROUP', $groupTab);
		$smarty->assign ('THEME', $theme);
		// Messages
		$smarty->assign ('OPERATING_MODES', $operatingMode);
		$smarty->assign ('AVAILABLE_MODULES', $availableModules);
		$smarty->assign ('MAIL_ACCOUNTS', (count ($mailAccounts)) ? $mailAccounts : null);
		$smarty->assign ('EMAILS_DATA', $emailsData);
		$smarty->assign ('TOTAL_UNREAD', $totalUnread);
		$smarty->display ('Home/MessagesListView.tpl');
		
	} catch (Exception $e) {
		$code   = $e->getCode ();
		$smarty = new vtigerCRM_Smarty ();
	}
