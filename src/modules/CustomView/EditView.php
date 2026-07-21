<?php
	require_once ('include/platzilla/Managers/ViewManager.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/CustomView/CustomView.php');
	require_once ('modules/CustomView/lib/CustomViewHelper.class.php');

	global $adb, $app_strings, $currentModule, $current_user, $mod_strings, $smarty, $theme;

	$thisAction = PlatzillaUtils::purify ($_REQUEST, 'action');
	$viewId     = PlatzillaUtils::purify ($_REQUEST, 'record');
	$parentTab  = isset ($_REQUEST ['parenttab']) ? vtlib_purify ($_REQUEST ['parenttab']) : null;
	$profileIds = isset ($_REQUEST ['profileids']) ? vtlib_purify ($_REQUEST ['profileids']) : null;
	$profileIds = !empty ($profileIds) ? explode (',', $profileIds) : null;

	// Custom View
	$oCustomView = new CustomView ($currentModule);
	$vm          = ViewManager::getInstance ($adb);
	$viewGroup   = $vm->fetchViewGroupByModule ($currentModule);
	if (isset ($_SESSION ['flashmessage']['data'])) {
		$view = unserialize ($_SESSION ['flashmessage']['data']);
		unset ($_SESSION ['flashmessage']['data']);
	}
	if (!empty ($viewId)) {
		$view         = $vm->fetchViewById ($currentModule, $viewId);
		if (empty ($view)) {
			$smarty->assign ('APP', $app_strings);
			$smarty->assign ('ICON_URL', vtiger_imageurl ('denied.gif', $theme));
			$smarty->display ('AccessDenied.tpl');
			exit ();
		}
	} else {
		$view = null;
	}

	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('AVAILABLE_COLUMNS', CustomViewHelper::getAvailableColumnsData ($adb, $oCustomView, $currentModule));
	$smarty->assign ('AVAILABLE_DATE_COLUMNS', CustomViewHelper::getAvailableDateColumnsData ($oCustomView, $currentModule, $current_user));
	$smarty->assign ('AVAILABLE_PERIODS', CustomViewHelper::getAvailablePeriods ());
	$smarty->assign ('CATEGORY', (!empty (getParentTab ())) ? getParentTab () : $parentTab);
	$smarty->assign ('CURRENT_MODULE', $currentModule);
	$smarty->assign ('CUSTOM_GROUP', $oCustomView->customViewDaughter);
	$smarty->assign ('IS_ADMIN', is_admin ($current_user));
	$smarty->assign ('IS_MOTHER', empty ($_SESSION ['platInstancia']));
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('MODULE_LABEL', getTranslatedString ($currentModule, $currentModule));
	$smarty->assign ('THEME', $theme);
	$smarty->assign ('VIEW_GROUP', $viewGroup);
	$smarty->assignByRef ('VIEW', $view);
	if (isset ($_SESSION ['flashmessage'])) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		unset ($_SESSION ['flashmessage']);
	}
	$smarty->display ('CustomView.tpl');
