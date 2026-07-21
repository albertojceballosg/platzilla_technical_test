<?php
	require_once ('include/utils/VtlibUtils.php');

	global $app_strings, $currentModule, $mod_strings, $smarty;
	checkFileAccessForInclusion ("modules/{$currentModule}/{$currentModule}.php");
	require_once ("modules/{$currentModule}/{$currentModule}.php");

	ini_set ('max_execution_time', '999');
	ini_set ('memory_limit', '128M');
	ini_set ('post_max_size', '60M');

	if(isset($_REQUEST['boxscoreid'])) {
		$boxScoreId = vtlib_purify($_REQUEST ['boxscoreid']);
	} else{
		$boxScoreId = null;
	}

	if(isset($_REQUEST['fecha_desde'])) {
		$from = vtlib_purify($_REQUEST['fecha_desde']);
	} else{
		$from = null;
	}

	if(isset($_REQUEST['monthsearch'])) {
		$monthSearch = vtlib_purify($_REQUEST['monthsearch']);
	} else{
		$monthSearch = null;
	}

	if(isset($_REQUEST ['fecha_hasta'])) {
		$to = vtlib_purify($_REQUEST['fecha_hasta']);
	} else{
		$to = null;
	}

	if(isset($_REQUEST ['tipo'])) {
		$type = vtlib_purify ($_REQUEST ['tipo']);
	} else{
		$type = null;
	}


	$bs = new box_score ();
	$bs->loadDefaultData ($boxScoreId);
	$blocks = $bs->getBlocks ();

	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('BLOCKS', $blocks);
	$smarty->assign ('BOX_SCORE', $bs);
	$smarty->assign ('BOX_SCORE_ID', $boxScoreId);
	$smarty->assign ('FROM', $from);
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('MONTH_SEARCH', $monthSearch);
	$smarty->assign ('TEMPLATE_PATH', 'themes/modern');
	$smarty->assign ('TO', $to);
	$smarty->assign ('TYPE', $type);
	$smarty->display ('modules/boxscore/EditViewBoxSemanal.tpl');
