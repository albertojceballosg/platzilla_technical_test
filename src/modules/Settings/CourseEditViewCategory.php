<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Courses/lib/CoursesHelper.php');

	global $adb, $currentModule, $mod_strings, $theme, $site_URL;

	setBugSnag ($site_URL);

	$record       = PlatzillaUtils::purify ($_GET, 'record', null);
	
	try {
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('CATEGORY', CourseManager::getInstance ($adb)->fetchCategoryById ($record));
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('RETURN_ACTION', $returnAction);
		$smarty->assign ('RETURN_MODULE', $returnModule);
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
		$smarty->display ('Settings/Course/EditViewCategory.tpl');
	} catch (Exception $e) {
		$smarty->assign ('HOW_USE', null);
		$smarty->assign ('IS_ERROR', true);
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->display ('Settings/Course/ListView.tpl');
	}
