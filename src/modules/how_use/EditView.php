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
		$howToUseObject = HowToUseHelper::getHowToUseById ($adb, $record);
		if (!empty ($howToUseObject)) {
			$howUseViews = $howToUseObject->getHowUseView ();
			if (!empty ($howUseViews)) {
				foreach ($howUseViews as $howUseView) {
					$viewRows['views'][]    = $howUseView->getRelatedViews();
					$viewRows['defaults'][] = $howUseView->getRelatedId();
					$viewRows['tabViews'][]  = $howUseView->getMasterView()->getViewName();
				}
			}
		}

		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('AVAILABLE_VIEW', HowToUseHelper::fetchMasterViews ($adb));
		$smarty->assign ('AVAILABLE_MODULES', HowToUseHelper::getAvailableModules ($adb));
		$smarty->assign ('AVAILABLE_STATUS', HowToUseInterface::HOW_TO_USE_STATUS);
		$smarty->assign ('HOW_USE', $howToUseObject);
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('RETURN_ACTION', $returnAction);
		$smarty->assign ('RETURN_MODULE', $returnModule);
		$smarty->assign ('VIEW_ROW', (isset($viewRows)) ? $viewRows : null);
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
		$smarty->display ('modules/how_use/EditView.tpl');
	} catch (Exception $e) {
		$smarty->assign ('HOW_USE', null);
		$smarty->assign ('IS_ERROR', true);
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->display ('modules/how_use/ListView.tpl');
	}
