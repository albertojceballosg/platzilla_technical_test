<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Courses/lib/CoursesHelper.php');
	
	global $adb, $currentModule, $mod_strings, $site_URL;
	
	setBugSnag ($site_URL);
	
	$page         = PlatzillaUtils::purify ($_GET, 'page', 1);
	$selectedTab  = PlatzillaUtils::purify ($_GET, 'tab', 'courses');
	$returnAction = PlatzillaUtils::purify ($_GET, 'return_action', 'ListView');
	$returnModule = PlatzillaUtils::purify ($_GET, 'return_module', $currentModule);
	
	$courseManager   = CourseManager::getInstance ($adb);
	
	$smarty = new vtigerCRM_Smarty ();
	try {
		$smarty->assign ('CATEGORIES', $courseManager->fetchCategories (false));
		$smarty->assign ('MICROCOURSES', $courseManager->fetchAllCourses ());
		$smarty->assign ('SERIES', $courseManager->fetchSeries (false));
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('RETURN_ACTION', $returnAction);
		$smarty->assign ('SELECTED_TAB', $selectedTab);
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
		$smarty->display ('Settings/Course/ListView.tpl');
	} catch (Exception $e) {
		$smarty->assign ('HOW_USE', null);
		$smarty->assign ('IS_ERROR', true);
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->display ('Settings/Course/ListView.tpl');
	}
