<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Managers/PlatformBillingPlanManager.php');
	require_once ('include/platzilla/Managers/PlatformFreeBillingPlanLimitManager.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $app_strings, $current_user, $mod_strings, $theme;

	$smarty     = new vtigerCRM_Smarty ();
	if ((!empty ($_SESSION ['platInstancia'])) || (!is_admin ($current_user))) {
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('ICON_URL', vtiger_imageurl ('denied.gif', $theme));
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}

	$selectedTab = PlatzillaUtils::purify ($_GET, 'tab');

	$smarty->assign ('LIMITS', PlatformFreeBillingPlanLimitManager::getInstance ($adb)->fetchLimits ());
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('PLANS', PlatformBillingPlanManager::getInstance ($adb)->fetchPlans ());
	$smarty->assign ('SELECTED_TAB', $selectedTab);
	if (isset ($_SESSION ['flashmessage'])) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		unset ($_SESSION ['flashmessage']);
	}
	$smarty->display ('Settings/PlatformBillingPlanListView.tpl');
