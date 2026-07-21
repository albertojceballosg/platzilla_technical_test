<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Settings/lib/HelpSettingsHelper.class.php');
	
	global $currentModule, $current_user, $platPrincipal, $mod_strings, $theme, $site_URL;
	
	setBugSnag ($site_URL);
	
	$fileId    = PlatzillaUtils::purify ($_GET, 'record', null);
	$helpField = null;
	$smarty = new vtigerCRM_Smarty ();
	if (!is_admin ($current_user)) {
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('ICON_URL', vtiger_imageurl ('denied.gif', $theme));
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}
	
	try {
		if (!empty($fileId)) {
			$helpField = HelpSettingsHelper::fetchHelpFieldById ($adb, $fileId);
		}
		
		$availableModules = HelpSettingsHelper::fetchAvailableModule ($adb);

		$smarty->assign ('AVAILABLE_MODULES', $availableModules);
		$smarty->assign ('AVAILABLE_FIELDS', HelpSettingsHelper::fetchAvailableFieldByModules ($adb, $availableModules));
		$smarty->assign ('HELP_FIELD', $helpField);
		$smarty->assign ('HELP_STATUS', HelpFieldConstants::$HELP_FIELD_STATUS);
		$smarty->assign ('IS_EDITABLE', HelpFieldConstants::$HELP_FIELD_EDITABLE);
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('TYPE_VIDEO', HelpFieldConstants::$HELP_FIELD_TYPE_VIDEO);
		$smarty->assign ('UPLOAD_MAXSIZE', (PlatzillaUtils::getMaxFileSizeInMb () * 1024 * 1024));
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
	} catch (Exception $e) {
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', 'index.php?module=materials&action=ListView&parenttab=Settings');
		$smarty->display ('Message.tpl');
	}
	$smarty->display ('Settings/HelpSettingsFieldEditView.tpl');
