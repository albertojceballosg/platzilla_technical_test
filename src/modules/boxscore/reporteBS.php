<?php
	require_once ('include/utils/VtlibUtils.php');
	require_once ('modules/boxscore/boxscore.php');
	require_once ('modules/PickList/PickListUtils.php');

	global $adb, $currentModule, $mod_strings, $smarty;

	if(isset($_REQUEST['boxscoreselect'])) {
		$boxScoreSelect = vtlib_purify($_REQUEST['boxscoreselect']);
	} else {
		$boxScoreSelect = null;
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
		$record = vtlib_purify($_REQUEST['record']);
	} else{
		$record = null;
	}

	if(isset($_REQUEST['fecha_hasta'])) {
		$to = vtlib_purify($_REQUEST['fecha_hasta']);
	} else{
		$to = null;
	}

	$boxScoreDataIds = implode (',', $kpis);

	$boxScoreIds = array ();
	if (!empty ($boxScoreSelect)) {
		foreach ($boxScoreSelect as $boxScoreId) {
			if (!empty ($boxScoreId)) {
				$boxScoreIds [] = $boxScoreId;
			}
		}
	}
	$boxScoreIds = implode (',', $boxScoreIds);

	$boxScoreBase         = array ();
	$boxScoreValues = array ();
	$boxScoreTitles = array ();
	if (!empty ($boxScoreSelect)) {
		foreach ($boxScoreSelect as $key => $value) {
			if ($value != '') {
				$boxScoreBase [ $value ] = array ('id' => $value);
			}
			$boxScoreValues [] = $value;

			$result = $adb->pquery ('SELECT titulo FROM vtiger_boxscore WHERE boxscoreid=?', array ($value));
			if (($result) && ($adb->num_rows ($result) > 0)) {
				$row   = $adb->fetchByAssoc ($result);
				$title = $row ['titulo'];
			} else {
				$title = '';
			}
			$boxScoreTitles [ $value ] = $title;
		}
	}

	$data = array ();
	if (!empty ($kpis)) {
		foreach ($kpis as $key => $value) {
			if ($value != '') {
				$data [ $value ] = array ('id' => $value, 'BSid' => $boxScoreBase);
			}
		}
	}

	$weeks = array ();
	$query      = "SELECT vbsd.* FROM vtiger_box_score_data vbsd WHERE vbsd.boxscoreid=$record AND vbsd.box_score_dataid IN ({$boxScoreDataIds})";
	$kpisResult = $adb->query ($query);
	if (($kpisResult) && ($adb->num_rows ($kpisResult) > 0)) {
		while ($kpi = $adb->fetchByAssoc ($kpisResult, -1, false)) {
			$data [ $kpi ['box_score_dataid'] ]['titulo'] = $kpi ['box_score'];
			$result = $adb->pquery (
				"SELECT * FROM vtiger_box_score_data vbsd JOIN vtiger_box_score_data_semanal bsds ON bsds.box_score_dataid=vbsd.box_score_dataid AND bsds.boxscoreid=vbsd.boxscoreid WHERE vbsd.boxscoreid in ({$boxScoreIds}) AND box_score=? AND fecha>=? AND fecha<=?",
				array ($kpi ['box_score'], $from, $to)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
			continue;
			}

			while ($row = $adb->fetchByAssoc ($result)) {
			$data [ $kpi ['box_score_dataid'] ]['BSid'][ $row ['boxscoreid'] ]['dataSemanal'][ $row ['fecha'] ] = $row ['valor'];
			if (!in_array ($row ['fecha'], $weeks)) {
					$weeks [] = $row ['fecha'];
			}
			}
		}
	}

	$smarty->assign ('BOX_SCORE_TITLES', $boxScoreTitles);
	$smarty->assign ('BOX_SCORE_VALUES', $boxScoreValues);
	$smarty->assign ('CURRENT_MODULE', $currentModule);
	$smarty->assign ('FROM', $from);
	$smarty->assign ('QUERY', $query);
	$smarty->assign ('RECORD', $record);
	$smarty->assign ('ROLES', getrole2picklist ());
	$smarty->assign ('TO', $to);
	$smarty->assign ('VARIABLES', $_REQUEST);
	$smarty->assign ('WEEKS', $weeks);
	$smarty->display ('modules/boxscore/BoxScoreReport.tpl');
