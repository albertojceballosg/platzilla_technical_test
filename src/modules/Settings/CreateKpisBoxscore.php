<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/Settings/lib/KpisHelper.class.php');

	global $adb, $current_language;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$keyword = (isset ($_SESSION ['queryFiltroForModule'])) && (!empty ($_SESSION ['queryFiltroForModule'])) ? $_SESSION ['queryFiltroForModule'] : null;

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('MOD', return_module_language ($current_language, 'Settings'));
	$smarty->assign ('MODULESFREE', KpisHelper::getVisibleModulesData ($adb, $keyword));
	$smarty->display ('Settings/CreateKpisBoxscore.tpl');
