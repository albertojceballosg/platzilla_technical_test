<?php
	require_once ('include/utils/VtlibUtils.php');

	global $adb, $currentModule;

	if(isset($_REQUEST ['delete'])) {
		$delete = vtlib_purify($_REQUEST ['delete']);
	} else{
		$delete = null;
	}

	if(isset($_REQUEST ['record'])) {
		$recordId = vtlib_purify($_REQUEST['record']);
	} else{
		$recordId = null;
	}

	if(isset($_REQUEST ['root'])) {
		$root = vtlib_purify($_REQUEST ['root']);
	} else{
		$root = null;
	}


	if ($root) {
		if (($delete) && (!empty ($recordId))) {
			$adb->pquery ("UPDATE vtiger_crmentity SET deleted = '1' WHERE crmid=?", array ($recordId));
			echo 'delete_on';
		} else {
			echo 'delete_of';
		}
	} else if (($delete) && (!empty ($recordId))) {
		$adb->pquery ('DELETE FROM vtiger_box_score_data WHERE box_score_dataid=?', array ($recordId));
	}
