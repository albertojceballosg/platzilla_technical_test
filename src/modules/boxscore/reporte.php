<?php
	require_once ('include/utils/VtlibUtils.php');
	require_once ('modules/boxscore/boxscore.php');
	require_once ('modules/PickList/PickListUtils.php');

	global $currentModule, $mod_strings, $smarty;

	if(isset($_REQUEST ['checkbox-comparar'])) {
		$compare = vtlib_purify($_REQUEST['checkbox-comparar']);
	} else {
		$compare = null;
	}

	if(isset($_REQUEST['fecha_desde'])) {
		$from = vtlib_purify($_REQUEST['fecha_desde']);
	} else{
		$from = null;
	}

	if(isset($_REQUEST['idsBS'])) {
		$kpis = vtlib_purify($_REQUEST['idsBS']);
	} else{
		$kpis = null;
	}

	if(isset($_REQUEST['record'])) {
		$record = vtlib_purify($_REQUES['record']);
	} else {
		$record = null;
	}

	if(isset($_REQUEST['fecha_hasta'])) {
		$to = vtlib_purify($_REQUEST['fecha_hasta']);
	} else {
		$to = null;
	}

	$bs = new box_score ();
	$bs->loadReportData ($record, $kpis);

	$weeks     = array ();
	$totalKpis = count ($bs->boxs);
	if ($totalKpis > 1) {
	$keys = array_keys ($bs->boxs [0]['semanal']);
	foreach ($keys as $key) {
		$weeks [ $key ] = array ();
		for ($i = 0; $i <= ($totalKpis - 1); $i++) {
			$weeks [ $key ][ $i ]['titulo'] = $bs->boxs [ $i ]['box_score'];
			$weeks [ $key ][ $i ]['fecha']  = $bs->boxs [ $i ]['semanal'][ $key ]['fecha'];
			$weeks [ $key ][ $i ]['valor']  = $bs->boxs [ $i ]['semanal'][ $key ]['valor'];
		}
	}
	}

	$smarty->assign ('BOX_SCORE', $bs);
	$smarty->assign ('COMPARE', $compare);
	$smarty->assign ('CURRENT_MODULE', $currentModule);
	$smarty->assign ('FROM', $from);
	$smarty->assign ('RECORD', $record);
	$smarty->assign ('ROLES', getrole2picklist ());
	$smarty->assign ('TO', $to);
	$smarty->assign ('WEEKS', $weeks);
	$smarty->display ('modules/boxscore/Report.tpl');
