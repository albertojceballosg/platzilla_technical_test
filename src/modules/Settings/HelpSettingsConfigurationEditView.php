<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Settings/lib/HelpSettingsHelper.class.php');

	global $adb, $app_strings, $current_user, $theme;

	$smarty = new vtigerCRM_Smarty();
	if ((!empty ($_SESSION ['platInstancia'])) || (!is_admin ($current_user))) {
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('ICON_URL', vtiger_imageurl ('denied.gif', $theme));
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}

	$availableBlockNames   = HelpSettingsHelper::fetchAvailableBlockNames ($adb);
	$availableSectionNames = HelpSettingsHelper::fetchAvailableSectionNames ($adb);
	$recordId              = PlatzillaUtils::purify ($_GET, 'record');
	if (isset ($_SESSION ['flashmessage']['data'])) {
		$configuration = $_SESSION ['flashmessage']['data'];
		unset ($_SESSION ['flashmessage']['data']);
	} else if (!empty ($recordId)) {
		$configuration = HelpSettingsHelper::fetchHelpConfiguration ($adb, $recordId);
	} else {
		$configuration = null;
	}

	$smarty->assign ('AVAILABLE_SECTION_NAMES', $availableSectionNames);
	$smarty->assign ('AVAILABLE_BLOCK_NAMES', $availableBlockNames);
	$smarty->assign ('CONFIGURATION', $configuration);
	if (isset ($_SESSION ['flashmessage'])) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		unset ($_SESSION ['flashmessage']);
	}
	$smarty->display ('Settings/HelpSettingsConfigurationEditView.tpl');
