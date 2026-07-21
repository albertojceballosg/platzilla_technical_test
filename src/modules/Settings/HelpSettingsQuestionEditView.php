<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Settings/lib/HelpSettingsHelper.class.php');

	global $adb, $app_strings, $current_user, $mod_strings, $theme;

	$smarty = new vtigerCRM_Smarty();
	if ((!empty ($_SESSION ['platInstancia'])) || (!is_admin ($current_user))) {
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('ICON_URL', vtiger_imageurl ('denied.gif', $theme));
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}

	$recordId = PlatzillaUtils::purify ($_GET, 'record');
	if (isset ($_SESSION ['flashmessage']['data'])) {
		$question = $_SESSION ['flashmessage']['data'];
		unset ($_SESSION ['flashmessage']['data']);
	} else if (!empty ($recordId)) {
		$question = HelpSettingsHelper::fetchHelpQuestion ($adb, $recordId);
	} else {
		$question = null;
	}
	$applications = HelpSettingsHelper::fetchApplications ($adb);

	$smarty->assign ('APPLICATIONS', $applications);
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('QUESTION', $question);
	if (isset ($_SESSION ['flashmessage'])) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		unset ($_SESSION ['flashmessage']);
	}
	$smarty->display ('Settings/HelpSettingsQuestionEditView.tpl');
