<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/how_use/lib/HowToUseHelper.php');

	global $adb, $currentModule, $mod_strings, $site_URL;

	setBugSnag ($site_URL);

	$page         = PlatzillaUtils::purify ($_GET, 'page', 1);
	$selectedTab  = PlatzillaUtils::purify ($_GET, 'tab', 'how_use');
	$returnAction = PlatzillaUtils::purify ($_GET, 'return_action', 'ListView');
	$returnModule = PlatzillaUtils::purify ($_GET, 'return_module', $currentModule);

	$phum   = ProfilesHowToUseManager::getInstance ($adb);
	$smarty = new vtigerCRM_Smarty ();
	try {
		$smarty->assign ('AVAILABLE_MODULES', HowToUseHelper::getAvailableModules ($adb));
		$smarty->assign ('COMPANY_PHASES', $phum->fetchCompanyPhases());
		$smarty->assign ('COMPANY_SECTOR', $phum->fetchCompanySector());
		$smarty->assign ('COMPANY_TYPES', $phum->fetchCompanyTypes());
		$smarty->assign ('HOW_USE', HowToUseHelper::fetchAllHowToUse ($adb, '',true));
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('PROFILES', $phum->fetchProfilesHowToUse (true));
		$smarty->assign ('RETURN_ACTION', $returnAction);
		$smarty->assign ('SELECTED_TAB', $selectedTab);
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
		$smarty->display ('modules/how_use/ListView.tpl');
	} catch (Exception $e) {
		$smarty->assign ('HOW_USE', null);
		$smarty->assign ('IS_ERROR', true);
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->display ('modules/how_use/ListView.tpl');
	}
