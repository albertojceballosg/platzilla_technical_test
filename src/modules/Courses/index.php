<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('modules/Courses/lib/CoursesHelper.php');
	
	$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('CATEGORIES', CoursesHelper::fetchCategories ($masterAdb));
	$smarty->assign ('COURSES', CoursesHelper::fetchCoursesByTargetAudience ($masterAdb, $_SESSION ['platInstancia']));
	$smarty->assign ('IS_INSTANCE', !empty ($_SESSION ['platInstancia']));
	if (isset ($_SESSION ['flashmessage'])) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		unset ($_SESSION ['flashmessage']);
	}
	$smarty->display ('modules/Courses/index.tpl');
