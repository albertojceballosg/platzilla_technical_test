<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Objects/BackgroundTaskFilter.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/backgroundtasks/lib/BackgroundTasksUtils.class.php');

	global $adb, $site_URL;
	setBugSnag ($site_URL);

	$filterId   = PlatzillaUtils::purify ($_GET, 'filterid');
	$groupId    = PlatzillaUtils::purify ($_GET, 'groupid');
	$moduleName = PlatzillaUtils::purify ($_GET, 'modulename');
	if (empty ($moduleName)) {
		echo '';
		exit ();
	}

	$filter = BackgroundTaskFilter::getInstance ()
		->setGroupId ($groupId)
		->setModuleName ($moduleName)
		->setSequence ($filterId);

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('AVAILABLE_FIELDS', BackgroundTasksUtils::getAvailableFieldsData ($adb, $moduleName));
	$smarty->assign ('AVAILABLE_USERS', BackgroundTasksUtils::getAvailableUsers ($adb, $_SESSION ['plat']));
	$smarty->assign ('FILTER', $filter);
	$smarty->assign ('IS_LAST_FILTER', true);
	$smarty->display ('modules/backgroundtasks/Filter.tpl');
	exit ();
