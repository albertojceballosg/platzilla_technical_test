<?php
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/VtlibUtils.php');

	global $currentModule, $mod_strings, $smarty;

	checkFileAccessForInclusion ("modules/{$currentModule}/{$currentModule}.php");
	require_once ("modules/{$currentModule}/{$currentModule}.php");

	if(isset($_REQUEST ['account_id'])) {
		$accountId = vtlib_purify ($_REQUEST ['account_id']);
	} else{
		$accountId = null;
	}

	if(isset($_REQUEST ['modeView'])) {
		$mode = vtlib_purify($_REQUEST ['modeView']);
	} else{
		$mode = null;
	}

	if(isset($_REQUEST ['record'])) {
		$record = vtlib_purify($_REQUEST ['record']);
	} else{
		$record = null;
	}

	if(isset($_REQUEST ['type'])) {
		$type = vtlib_purify($_REQUEST['type']);
	} else{
		$type = null;
	}


	if ($mode == 'edit') {
		$ebs = new box_score ();
		$calculation = $ebs->getCalculation ($record);
		$ebs->loadBasicDataByBoxScoreId ($accountId);
	} else {
		$ebs = null;
		$calculation = null;
	}

	$bsx = new box_score ();
	$bsx->loadBasicDataByBoxScoreId ($accountId);

	$smarty->assign ('ACCOUNT_ID', $accountId);
	$smarty->assign ('BOX_SCORE', $bsx);
	$smarty->assign ('CALCULATION', $calculation);
	$smarty->assign ('EDITABLE_BOX_SCORE', $ebs);
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('MODE', $mode);
	$smarty->assign ('RECORD', $record);
	$smarty->assign ('TEMPLATE_PATH', 'themes/modern');
	$smarty->assign ('TYPE', $type);
	$smarty->display ('modules/Settings/EditViewBoxCalc.tpl');
