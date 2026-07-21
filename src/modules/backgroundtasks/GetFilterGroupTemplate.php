<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Objects/BackgroundTaskFilter.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/backgroundtasks/lib/BackgroundTasksUtils.class.php');

	global $adb, $site_URL;
	setBugSnag ($site_URL);

	$groupId = PlatzillaUtils::purify ($_GET, 'groupid');
	$moduleName = PlatzillaUtils::purify ($_GET, 'modulename');
	if (empty ($moduleName)) {
		echo '';
		exit ();
	}

	$group = BackgroundTaskFilterGroup::getInstance ()
		->setId ($groupId)
		->setFilters (array (BackgroundTaskFilter::getInstance ()->setGroupId ($groupId)->setModuleName ($moduleName)->setSequence (-1)))
		->setModuleName ($moduleName);

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('AVAILABLE_FIELDS', BackgroundTasksUtils::getAvailableFieldsData ($adb, $moduleName));
	$smarty->assign ('AVAILABLE_USERS', BackgroundTasksUtils::getAvailableUsers ($adb, $_SESSION ['plat']));
	$smarty->assign ('GROUP', $group);
	$smarty->display ('modules/backgroundtasks/FilterGroup.tpl');
	exit ();
