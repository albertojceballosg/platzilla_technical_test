<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $mod_strings;

	$moduleName = PlatzillaUtils::purify ($_REQUEST, 'return_module');

	$smarty = new vtigerCRM_Smarty();
	$smarty->assign ('MAX_FILE_SIZE', PlatzillaUtils::getMaxFileSizeInMb ());
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('MODULE_NAME', $moduleName);
	$smarty->display ('modules/Import/ImportTemplate1.tpl');
