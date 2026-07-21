<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Settings/lib/HelpSettingsHelper.class.php');

	global $adb, $mod_strings;

	$recordId = PlatzillaUtils::purify ($_REQUEST, 'record');

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('APPLICATIONS', HelpSettingsHelper::fetchApplications ($adb));

	$smarty->assign ('MOD', $mod_strings);
	if (isset ($_SESSION ['flashmessage'])) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		$smarty->assign ('HELP_ITEM', $_SESSION ['flashmessage']['data']);
		unset ($_SESSION ['flashmessage']);
	} else {
		$smarty->assign ('HELP_ITEM', HelpSettingsHelper::fetchHelpItem ($adb, $recordId));
	}
	$smarty->display ('Settings/HelpSettingsEditView.tpl');
