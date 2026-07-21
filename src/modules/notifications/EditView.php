<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/notifications/lib/NotificationUtils.class.php');
	require_once ('modules/notifications/lib/NotificationPeriodUtils.class.php');
	// Agregado por EB para integrar BUGSNAG - 20200213
	global $site_URL;
	require_once ('include/bugsnag-php-2.9.2/src/Bugsnag/Autoload.php');
	$bugsnag = new Bugsnag_Client('834d564193a48c47f138dc66d2cf5e83');
	$bugsnag->setAppVersion('1.0.0');
	if ($site_URL == 'https://apphome.platzillatest.com/') {
		$bugsnag->setReleaseStage('https://apphome.platzillatest.com/');
	} else if ($site_URL == 'https://app.platzilla.com/') {
		$bugsnag->setReleaseStage('https://app.platzilla.com/');
	} else {
		$bugsnag->setReleaseStage($site_URL);
	}
	$bugsnag->setErrorReportingLevel(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_WARNING & ~E_DEPRECATED);
	// Agregado por EB para integrar BUGSNAG - 20200213

	global $adb, $app_strings, $current_user, $mod_strings, $theme;

	$smarty = new vtigerCRM_Smarty ();

	try {
		$notificationId  = PlatzillaUtils::purify ($_GET, 'record');
		$notification    = null;
		$fieldModuleList = null;
		$filterType      = null;
		$isInstance      = !empty ($_SESSION ['platInstancia']);

		if (isset ($_SESSION ['flashmessage']['data'])) {
			$notification = Notification::getInstance ();
			$notification->unserialize ($_SESSION ['flashmessage']['data']);
		} else if (!empty ($notificationId)) {
			$notification    = NotificationUtils::fetchNotification ($adb, $notificationId);
			$fieldModuleList = NotificationUtils::getColumnsByModule ($adb, $notification->getFilter ()->getModuleFilter ());
			if ($notification->getModal ()) {
				$buttonIds = (!empty ($notification->getModal ())) ? json_decode ($notification->getModal()->getCustomButton()) : null;
				$notification->getModal ()->setButtonLinks (NotificationUtils::fetchCustomButtonsData ($adb, $buttonIds));
			}

			$filterType = NotificationUtils::getTypeOfData ();
		}

		$smarty->assign ('AVAILABLE_ACTIONS', NotificationUtils::getAvailableActions ());
		$smarty->assign ('AVAILABLE_EVENTS', NotificationUtils::getAvailableEvents ());
		$smarty->assign ('AVAILABLE_FROM', NotificationUtils::getAvailableFrom ());
		$smarty->assign ('AVAILABLE_MODULES', NotificationUtils::fetchAvailableEntityModules ($adb));
		$smarty->assign ('AVAILABLE_PERIODS', NotificationPeriodUtils::getAvailablePeriods ());
		$smarty->assign ('AVAILABLE_STATUSES', NotificationUtils::getAvailableStatuses ());
		$smarty->assign ('AVAILABLE_STYLE', NotificationUtils::getAvailableStyle ());
		$smarty->assign ('AVAILABLE_TYPES', NotificationUtils::getAvailableTypes ());
		$smarty->assign ('AVAILABLE_VIEWS', NotificationUtils::getAvailableViews ());
		$smarty->assign ('CUSTOMBUTTONS', NotificationUtils::fetchCustomButtons ($adb, $isInstance));
		$smarty->assign ('FIELD_LIST', $fieldModuleList);
		$smarty->assign ('IS_INSTANCE', !empty ($_SESSION ['platInstancia']) ? true : false);
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('NOTIFICATION', $notification);
		$smarty->assign ('RECORD', $notificationId);
		$smarty->assign ('FILTER_TYPE', $filterType);
		$smarty->assign ('USERS', NotificationUtils::getUsers ($adb));
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
		$smarty->display ('modules/notifications/EditView.tpl');
	} catch (Exception $e) {
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', 'index.php?module=notifications&action=ListView&parenttab=Settings');
		$smarty->display ('Message.tpl');
	}
