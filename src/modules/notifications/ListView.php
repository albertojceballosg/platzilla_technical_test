<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/notifications/lib/NotificationUtils.class.php');
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

	global $adb, $current_user, $mod_strings;

	$keyword     = PlatzillaUtils::purify ($_GET, 'keyword');
	$page        = PlatzillaUtils::purify ($_GET, 'page');
	$rowsPerPage = 25;
	$scope       = (!empty ($_SESSION ['platInstancia'])) ? 'USERS' : '';

	$smarty = new vtigerCRM_Smarty();
	$smarty->assign ('DATA', NotificationUtils::fetchNotifications ($adb, $keyword, $page, $rowsPerPage, $scope));
	$smarty->assign ('DATA_ALL', NotificationUtils::fetchNotifications ($adb, null, null, null, $scope));
	$smarty->assign ('AVAILABLE_STYLE', NotificationUtils::getAvailableStyle ());
	$smarty->assign ('AVAILABLE_TYPES', NotificationUtils::getAvailableTypes ());
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('PAGE', $page);
	$smarty->assign ('SEARCH_KEYWORD', $keyword);
	if (isset ($_SESSION ['flashmessage'])) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		unset ($_SESSION ['flashmessage']);
	}
	$smarty->display ('modules/notifications/ListView.tpl');
