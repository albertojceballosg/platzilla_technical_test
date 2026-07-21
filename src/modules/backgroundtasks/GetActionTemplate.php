<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Objects/BackgroundTaskAction.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/backgroundtasks/lib/BackgroundTasksUtils.class.php');

	global $adb, $mod_strings, $site_URL;
	setBugSnag ($site_URL);

	$actionId = PlatzillaUtils::purify ($_GET, 'actionid');
	$scope    = PlatzillaUtils::purify ($_GET, 'scope');

	$action = BackgroundTaskAction::getInstance ()
		->setScope ($scope)
		->setSequence (intval ($actionId));

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('AVAILABLE_ACTIONS', BackgroundTasksUtils::getAvailableActions ($adb));
	$smarty->assign ('ACTION', $action);
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('taskScope', $scope);
	$smarty->display ('modules/backgroundtasks/Action.tpl');
	exit ();
