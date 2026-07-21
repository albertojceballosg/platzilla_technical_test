<?php
	require_once ('include/utils/VtlibUtils.php');
	require_once ('modules/boxscore/boxscore.php');

	global $adb, $currentModule, $mod_strings, $smarty;

	if(isset($_REQUEST['fecha_desde'])) {
		$from = vtlib_purify($_REQUEST['fecha_desde']);
	} else{
		$from = null;
	}

	if(isset ($_REQUEST['record'])) {
		$record = vtlib_purify($_REQUEST['record']);
	} else{
		$record = null;
	}

	if(isset($_REQUEST['fecha_hasta'])) {
		$to = vtlib_purify ($_REQUEST ['fecha_hasta']);
	} else{
		$to = null;
	}

	$boxScores = array ();
	$result    = $adb->query ('SELECT bs.boxscoreid, bs.titulo FROM vtiger_boxscore bs INNER JOIN vtiger_crmentity crme ON crme.crmid=bs.boxscoreid WHERE crme.deleted=0');
	if (($result) && ($adb->num_rows ($result) > 0)) {
		while ($row = $adb->fetchByAssoc ($result, -1, false)) {
			$boxScores [] = $row;
		}
	}

	$bs = new box_score ();
	if (!empty($record)) {
	$bs->loadDefaultData ($record);
	}

	$smarty->assign ('ACCOUNT_ID', $record);
	$smarty->assign ('BOX_SCORE', $bs);
	$smarty->assign ('BOX_SCORES', $boxScores);
	$smarty->assign ('CURRENT_MODULE', $currentModule);
	$smarty->assign ('FROM', $from);
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('RECORD', $record);
	$smarty->assign ('TO', $to);
	$smarty->display ('modules/boxscore/KPIList.tpl');
