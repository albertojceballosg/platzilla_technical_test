<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Settings/lib/HelpSettingsHelper.class.php');
    require_once ('modules/Settings/lib/HowToHelper.class.php');

	global $adb, $app_strings, $current_user, $mod_strings, $theme;
 
	$smarty = new vtigerCRM_Smarty();
	if ((!empty ($_SESSION ['platInstancia'])) || (!is_admin ($current_user))) {
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('ICON_URL', vtiger_imageurl ('denied.gif', $theme));
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}

	$selectedTab = PlatzillaUtils::purify ($_GET, 'tab');

	$applications  = HelpSettingsHelper::fetchApplications ($adb);
	$configuration = HelpSettingsHelper::fetchHelpConfigurations ($adb);
    $fields        = HelpSettingsHelper::fetchHelpField ($adb);
    $howTo         = HowToHelper::fetchHowTo ($adb);
	$questions     = HelpSettingsHelper::fetchHelpQuestions ($adb);
	$tips          = HelpSettingsHelper::fetchHelpTips ($adb);
	$tutorials     = HelpSettingsHelper::fetchHelpTutorials ($adb);
	$useCases      = HelpSettingsHelper::fetchHelpUseCases ($adb);
 
	$smarty->assign ('AVAILABLE_MODULES', HelpSettingsHelper::fetchAvailableModule ($adb));
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('APPLICATIONS', $applications);
	$smarty->assign ('CONFIGURATION', $configuration);
	$smarty->assign ('HELP_FIELDS', $fields);
    $smarty->assign ('HOW_TO', $howTo);
	$smarty->assign ('QUESTIONS', $questions);
	$smarty->assign ('SELECTED_TAB', $selectedTab);
	$smarty->assign ('TIPS', $tips);
	$smarty->assign ('TUTORIALS', $tutorials);
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('USE_CASES', $useCases);
	if (isset ($_SESSION ['flashmessage'])) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		unset ($_SESSION ['flashmessage']);
	}
	$smarty->display ('Settings/HelpSettingsListView.tpl');
