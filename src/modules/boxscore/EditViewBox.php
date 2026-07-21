<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/VtlibUtils.php');

	global $app_strings, $current_user, $currentModule, $mod_strings;

	checkFileAccessForInclusion ("modules/{$currentModule}/{$currentModule}.php");
	require_once ("modules/{$currentModule}/{$currentModule}.php");

	if(isset($_REQUEST['account_id'])) {
		$accountId = vtlib_purify($_REQUEST['account_id']);
	} else{
		$accountId = null;
	}

	if(isset($_REQUEST ['box_score'])) {
		$boxScore = vtlib_purify($_REQUEST['box_score']);
	} else{
		$boxScore = null;
	}

	if(isset($_REQUEST['monthsearch'])) {
		$monthSearch = vtlib_purify($_REQUEST['monthsearch']);
	} else{
		$monthSearch = null;
	}

	if(isset($_REQUEST['record'])) {
		$record = vtlib_purify($_REQUEST['record']);
	} else{
		$record = null;
	}

	if(isset($_REQUEST['submit'])) {
		$submit = vtlib_purify($_REQUEST ['submit']);
	} else{
		$submit = null;
	}

	if(isset($_REQUEST['type'])) {
		$type = vtlib_purify($_REQUEST['type']);
	} else{
		$type = null;
	}


	$bs = new box_score ();
	if (($submit) && (!empty ($boxScore))) {
		$bs->add ($_REQUEST);
	}
	if (!empty ($record)) {
		$bs->loadDefaultData ($accountId, $record);
		$fulfillment = $bs->boxs [0]['cump_array'];
	} else {
		$fulfillment = null;
	}

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('ACCOUNT_ID', $accountId);
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('BOX_SCORE', $bs);
	$smarty->assign ('COUNT', $current_user);
	$smarty->assign ('CURRENT_USER', $current_user);
	$smarty->assign ('FULFILLMENT', $fulfillment);
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('MONTH_SEARCH', $monthSearch);
	$smarty->assign ('RECORD', $record);
	$smarty->assign ('TEMPLATE_PATH', 'themes/modern');
	$smarty->assign ('TYPE', $type);
	$smarty->display ('modules/boxscore/EditViewBox.tpl');
