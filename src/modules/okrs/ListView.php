<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/okrs/lib/OkrHelperUtils.php');

	global $adb, $currentModule, $mod_strings, $site_URL;

	setBugSnag ($site_URL);

	$page         = PlatzillaUtils::purify ($_GET, 'page', 1);
	$selectedTab  = PlatzillaUtils::purify ($_GET, 'tab', 'objectives');
	$returnAction = PlatzillaUtils::purify ($_GET, 'return_action', 'ListView');
	$returnModule = PlatzillaUtils::purify ($_GET, 'return_module', $currentModule);
	
	try {
		$okrClass   = OkrHelperUtils::getInstance ();
		$objectives = $okrClass->fetchObjectives (null, true);
		if (!empty($objectives)) {
			$objectiveItem   = 0;
			$arrayObjectives = array();
			foreach ($objectives as $objective) {
				$arrayObjectives[ $objective->getId () ] = $objective->getToDo ();
				if (count ($objective->getCompanyTypes ())) {
					foreach ($objective->getCompanyTypes () as $type) {
						$translator[] = $mod_strings[ $type ];
					}
					$objectives[ $objectiveItem ]->setListTypes (implode (', ', $translator));
					unset($translator);
				}
				if (count ($objective->getCompanyPhases ())) {
					foreach ($objective->getCompanyPhases () as $phase) {
						$translator[] = $mod_strings[ $phase ];
					}
					$objectives[ $objectiveItem ]->setListPhases (implode (', ', $translator));
					unset($translator);
				}
				$objectiveItem++;
			}
			
		}
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('ARRAY_OBJECTIVES', isset($arrayObjectives) ? $arrayObjectives : null);
		$smarty->assign ('COMPANY_AREAS', OkrsInterface::OKRS_COMPANY_AREA);
		$smarty->assign ('COMPANY_PHASE', OkrsInterface::OKRS_COMPANY_PHASE);
		$smarty->assign ('COMPANY_TYPE', OkrsInterface::OKRS_COMPANY_TYPE);
		$smarty->assign ('KEY_RESULTS', $okrClass->fetchKeyResults ());
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('OBJECTIVES', $objectives);
		$smarty->assign ('RETURN_ACTION', $returnAction);
		$smarty->assign ('SELECTED_TAB', $selectedTab);
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
		$smarty->display ('modules/Okrs/ListView.tpl');
	} catch (Exception $e) {
		$smarty->assign ('OBJECTIVES', null);
		$smarty->assign ('ARRAY_OBJECTIVES', null);
		$smarty->assign ('KEY_RESULTS', null);
		$smarty->assign ('IS_ERROR', true);
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->display ('modules/Okrs/ListView.tpl');
	}
