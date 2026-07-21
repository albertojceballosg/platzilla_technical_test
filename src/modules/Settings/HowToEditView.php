<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Settings/lib/HowToHelper.class.php');
    require_once ('modules/Settings/lib/HelpSettingsHelper.class.php');
	
	global $adb, $app_strings, $currentModule, $current_user, $platPrincipal, $mod_strings, $theme, $site_URL;
	
	setBugSnag ($site_URL);
	
	$howToId = PlatzillaUtils::purify ($_GET, 'record', null);
	$smarty = new vtigerCRM_Smarty ();
	if (!is_admin ($current_user)) {
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('ICON_URL', vtiger_imageurl ('denied.gif', $theme));
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}
	
	try {
		if (!empty ($howToId)) {
			$howTo = HowToHelper::fetchHowToById ($adb, $howToId);
		}
		$modules = HelpSettingsHelper::fetchAvailableModule ($adb);
		if (!empty ($modules)) {
			$availableModules [] = array ('name' => 'Calendar', 'label' =>'Tareas de calendario');
			foreach ($modules as $module) {
				$availableModules [] = array ('name' => $module->getName (), 'label' => ucfirst ($module->getLabel ()));
				
			}
			usort($availableModules, function($a, $b) {
			  return strcmp ($a['label'], $b['label']);
			});
		}
		$smarty->assign ('AVAILABLE_MODULES', $availableModules);
		$smarty->assign ('HOW_TO', isset($howTo) ? $howTo : null);
		$smarty->assign ('HOW_TO_STATUS', HowToInterface::HOW_TO_STATUS);
		$smarty->assign ('HOW_TO_FILES', HowToInterface::HOW_TO_FILES);
		$smarty->assign ('MOD', $mod_strings);
        $smarty->assign('RECORD', $howToId);
		$smarty->assign ('TYPE_VIDEO', HowToInterface::HOW_TO_FIELD_TYPE_VIDEO);
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
	$smarty->display ('Settings/HowToEditView.tpl');

