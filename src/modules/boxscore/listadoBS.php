<?php
	require_once ('include/utils/VtlibUtils.php');
	require_once ('modules/boxscore/boxscore.php');

	global $adb, $currentModule, $smarty;

	$from = isset ($_REQUEST ['fecha_desde']) ? vtlib_purify ($_REQUEST ['fecha_desde']) : null;
	$record = isset ($_REQUEST ['record']) ? vtlib_purify ($_REQUEST ['record']) : null;
	$to = isset ($_REQUEST ['fecha_hasta']) ? vtlib_purify ($_REQUEST ['fecha_hasta']) : null;

	$boxScores = array ();
	$result  = $adb->query ('SELECT bs.boxscoreid, bs.titulo FROM vtiger_boxscore bs INNER JOIN vtiger_crmentity crme ON crme.crmid=bs.boxscoreid WHERE crme.deleted=0');
	if (($result) && ($adb->num_rows ($result) > 0)) {
		while ($row = $adb->fetchByAssoc ($result, -1, false)) {
			$boxScores [] = $row;
		}
	}

	$bs = new box_score ();
	$bs->loadData ($record);

	$smarty->assign ('ACCOUNT_ID', $record);
	$smarty->assign ('BOX_SCORE', $bs);
	$smarty->assign ('BOX_SCORES', $boxScores);
	$smarty->assign ('CURRENT_MODULE', $currentModule);
	$smarty->assign ('FROM', $from);
	$smarty->assign ('RECORD', $record);
	$smarty->assign ('TO', $to);
	$smarty->display ('modules/boxscore/BoxScoreList.tpl');
