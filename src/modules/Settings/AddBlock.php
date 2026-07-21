<?php
	require_once ('Smarty_setup.php');
	require_once ('modules/Settings/lib/AddBlockHelper.class.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	$moduleName       = SettingsUtils::purify ($_REQUEST, 'fld_module', '');
	$languageFilePath = "modules/$moduleName/language/{$_SESSION ['authenticated_user_language']}.lang.php";
	if (file_exists ($languageFilePath)) {
		require ($languageFilePath);
	}

	global $adb, $app_strings, $currentModule, $mod_strings, $theme;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$mode        = SettingsUtils::purify ($_REQUEST, 'mode');
	$tabId       = SettingsUtils::purify ($_REQUEST, 'tabid', '');
	$blockId     = SettingsUtils::purify ($_REQUEST, 'blockid', '');
	$blockSelect = SettingsUtils::purify ($_REQUEST, 'blockselect', '');
	$blockLabel  = AddBlockHelper::getBlockLabel ($adb, $blockId);
	$blocks      = AddBlockHelper::getModuleBlocks ($adb, $moduleName, $tabId);

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('APPLICATION_STRINGS', $app_strings);
	$smarty->assign ('BLOCK_ID', $blockId);
	$smarty->assign ('BLOCK_LABEL', $blockLabel);
	$smarty->assign ('BLOCK_SELECT', $blockSelect);
	$smarty->assign ('CURRENT_MODULE', $currentModule);
	$smarty->assign ('MODE', $mode);
	$smarty->assign ('MODULE', $moduleName);
	$smarty->assign ('TAB_ID', $tabId);
	$smarty->assign ('URL_IMAGE_CLOSE', vtiger_imageurl ('close.gif', $theme));
	echo $smarty->fetch ('Settings/AddBlock.tpl');
