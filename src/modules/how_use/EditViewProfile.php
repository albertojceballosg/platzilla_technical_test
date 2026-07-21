<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/how_use/lib/HowToUseHelper.php');

	global $adb, $currentModule, $mod_strings, $theme, $site_URL;

	setBugSnag ($site_URL);

	$record       = PlatzillaUtils::purify ($_GET, 'record');
	$returnAction = PlatzillaUtils::purify ($_GET, 'return_action', 'ListView');
	$returnModule = PlatzillaUtils::purify ($_GET, 'return_module', $currentModule);

	try {
		$phum    = ProfilesHowToUseManager::getInstance($adb);
		$profile = $phum->fetchProfilesHowToUseById ($record);
		if (!empty($profile)) {
			if (!empty($profile->getHowToUse ())) {
				$howUseProfiles = array ();
				foreach ($profile->getHowToUse () as $howToUse) {
					$howUseProfiles[ $howToUse->getTabName () ] = $howToUse->getHowUseName();
				}
			}
		}

		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('AVAILABLE_MODULES', HowToUseHelper::getAvailableModules ($adb));
		$smarty->assign ('AVAILABLE_STATUS', ProfilesHowToUseInterface::PROFILE_USE_STATUS);
		$smarty->assign ('COMPANY_PHASES', $phum->fetchCompanyPhases ());
		$smarty->assign ('COMPANY_SECTOR', $phum->fetchCompanySector ());
		$smarty->assign ('COMPANY_TYPES', $phum->fetchCompanyTypes());
		$smarty->assign ('HOW_USE', HowToUseHelper::fetchAllHowToUse ($adb, '',true));
		$smarty->assign ('PROFILES_HOW_USE', (isset ($howUseProfiles)) ? $howUseProfiles : null);
		$smarty->assign ('PROFILE_USE', $profile);
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('RETURN_ACTION', $returnAction);
		$smarty->assign ('RETURN_MODULE', $returnModule);
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
		$smarty->display ('modules/how_use/EditViewProfiles.tpl');
	} catch (Exception $e) {
		$smarty->assign ('HOW_USE', null);
		$smarty->assign ('IS_ERROR', true);
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->display ('modules/how_use/ListView.tpl');
	}
