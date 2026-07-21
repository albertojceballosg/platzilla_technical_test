<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/ListViewUtils.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb, $app_strings, $current_language, $mod_strings, $theme;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$templateId = SettingsUtils::purify ($_REQUEST, 'templateid');

	$result = $adb->pquery ('SELECT * FROM vtiger_emailtemplates WHERE templateid=?', array ($templateId));
	if (($result) && ($adb->num_rows ($result) > 0)) {
		$emailTemplate = $adb->fetchByAssoc ($result);
		$body          = decode_html ($emailTemplate ['body']);
		$description   = $emailTemplate ['description'];
		$folderName    = $emailTemplate ['foldername'];
		$subject       = $emailTemplate ['subject'];
		$templateId    = $emailTemplate ['templateid'];
		$templateName  = $emailTemplate ['templatename'];
	} else {
		$body         = '';
		$description  = '';
		$folderName   = '';
		$subject      = '';
		$templateId   = '';
		$templateName = '';
	}

	$smarty = new vtigerCRM_smarty ();
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('BODY', $body);
	$smarty->assign ('DESCRIPTION', $description);
	$smarty->assign ('FOLDERNAME', $folderName);
	$smarty->assign ('IMAGE_PATH', "themes/{$theme}/images/");
	$smarty->assign ('MOD', return_module_language ($current_language, 'Settings'));
	$smarty->assign ('MODULE', 'Settings');
	$smarty->assign ('SUBJECT', $subject);
	$smarty->assign ('TEMPLATEID', $templateId);
	$smarty->assign ('TEMPLATENAME', $templateName);
	$smarty->assign ('THEME', $theme);
	$smarty->assign ('UMOD', $mod_strings);
	$smarty->display ('DetailViewEmailTemplate.tpl');
