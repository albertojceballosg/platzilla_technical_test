<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/okrs/lib/OkrHelperUtils.php');

	global $adb, $currentModule, $mod_strings, $theme, $site_URL;

	setBugSnag ($site_URL);
	
	$objectiveId  = PlatzillaUtils::purify ($_GET, 'objective', null);
	$record       = PlatzillaUtils::purify ($_GET, 'record', null);
	$returnAction = PlatzillaUtils::purify ($_GET, 'return_action', 'EditViewKeyResult');
	$returnModule = PlatzillaUtils::purify ($_GET, 'return_module', $currentModule);
	$selectedTab  = PlatzillaUtils::purify ($_GET, 'tab', 'key_results');

	try {
		$okrClass  = OkrHelperUtils::getInstance ();
		if (!empty ($record)) {
			$keyResult = $okrClass->getKeyResultById ($record);
			if (!empty ($keyResult)) {
				$objective = $okrClass->getObjectiveById ($keyResult->getObjectiveId ());
			}
		}
		
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('AVAILABLE_STATUS', OkrsInterface::OKRS_STATUS);
		$smarty->assign ('FREQUENCY', OkrsInterface::OKRS_FREQUENCY);
		$smarty->assign ('KEY_RESULT', isset($keyResult) ? $keyResult : null);
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('OBJECTIVE', isset($objective) ? $objective : null);
		$smarty->assign ('OBJECTIVE_ID', $objectiveId);
		$smarty->assign ('OBJECTIVES', $okrClass->fetchObjectives ());
		$smarty->assign ('RETURN_ACTION', $returnAction);
		$smarty->assign ('RETURN_MODULE', $returnModule);
		$smarty->assign ('SELECTED_TAB', $selectedTab);
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
		$smarty->display ('modules/Okrs/EditViewKeyResult.tpl');
	} catch (Exception $e) {
		$smarty->assign ('KEY_RESULT', null);
		$smarty->assign ('OBJECTIVE', null);
		$smarty->assign ('IS_ERROR', true);
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->display ('modules/Okrs/EditViewKeyResult.tpl');
	}
