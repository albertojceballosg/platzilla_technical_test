<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/okrs/lib/OkrHelperUtils.php');

	global $adb, $currentModule, $mod_strings, $theme, $site_URL;

	setBugSnag ($site_URL);

	$record       = PlatzillaUtils::purify ($_GET, 'record', null);
	$returnAction = PlatzillaUtils::purify ($_GET, 'return_action', 'ListView');
	$returnModule = PlatzillaUtils::purify ($_GET, 'return_module', $currentModule);
	$selectedTab  = PlatzillaUtils::purify ($_GET, 'tab', 'objectives');

	try {
		
		if (!empty($record)) {
			$okrClass  = OkrHelperUtils::getInstance ();
			$objective = $okrClass->getObjectiveById ($record);
		}
		
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('AVAILABLE_STATUS', OkrsInterface::OKRS_STATUS);
		$smarty->assign ('COMPANY_AREAS', OkrsInterface::OKRS_COMPANY_AREA);
		$smarty->assign ('COMPANY_PHASE', OkrsInterface::OKRS_COMPANY_PHASE);
		$smarty->assign ('COMPANY_TYPE', OkrsInterface::OKRS_COMPANY_TYPE);
		$smarty->assign ('FREQUENCY', OkrsInterface::OKRS_FREQUENCY);
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('OBJECTIVE', isset($objective) ? $objective : null);
		$smarty->assign ('IS_ON_BOARDIMG', OkrsInterface::OKRS_IS_ON_BORDING);
		$smarty->assign ('RETURN_ACTION', $returnAction);
		$smarty->assign ('RETURN_MODULE', $returnModule);
		$smarty->assign ('SELECTED_TAB', $selectedTab);
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
		$smarty->display ('modules/Okrs/EditViewObjetives.tpl');
	} catch (Exception $e) {
		$smarty->assign ('OBJECTIVE', null);
		$smarty->assign ('IS_ERROR', true);
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->display ('modules/Okrs/EditViewObjetives.tpl');
	}
