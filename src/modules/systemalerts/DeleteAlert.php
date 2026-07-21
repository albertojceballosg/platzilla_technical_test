<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/systemalerts/lib/SystemAlertsHelper.class.php');
	require_once ('modules/indicatorspanel/lib/IndicatorsPanelHelper.class.php');

	global $adb;

	$delete          = PlatzillaUtils::purify ($_REQUEST, 'delete');
	$recordId        = PlatzillaUtils::purify ($_REQUEST, 'record');
	$recordIndicator = PlatzillaUtils::purify ($_REQUEST, 'indicatorRecord');

	if (($delete) && (!empty ($recordId)) && (!empty ($recordIndicator))) {
		$row    = IndicatorsPanelHelper::getDataIdRel ($adb, $recordIndicator);
		$result = $adb->pquery ('DELETE FROM vtiger_systemalerts WHERE indicator_id IN (?,?)', array ($recordIndicator, $row['box_score_dataid']));
		if ($result) {
			echo 'delete_on';
		} else {
			echo 'delete_off';
		}
	} else if (($delete) && (!empty ($recordId))) {
		$result = $adb->pquery ('DELETE FROM vtiger_systemalerts WHERE systemalerts_id  = ?', array ($recordId));
		if ($result) {
			echo 'delete_on';
		} else {
			echo 'delete_off';
		}
	} else {
		echo 'delete_off';
	}
