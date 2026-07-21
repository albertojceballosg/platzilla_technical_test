<?php
	require_once('include/logging.php');
	require_once('include/database/PearDatabase.php');
	$folderid = vtlib_purify($_REQUEST['folderid']);

	if(isset($_REQUEST['idlist']) && $_REQUEST['idlist']!= '') {
	$id_array = array();
	$id_array = explode(':',$_REQUEST['idlist']);
	$countIdArray = (count($id_array)-1);
	for ($i = 0; $countIdArray; $i++) {
		changeFolder($id_array[$i],$folderid);
	}
	header('Location: index.php?action=ReportsAjax&file=ListView&mode=ajaxdelete&module=Reports');
	} else if (isset($_REQUEST['record']) && $_REQUEST['record']!= '') {
	$id = vtlib_purify($_REQUEST['record']);
	changeFolder($id,$folderid);
	header('Location: index.php?action=ReportsAjax&file=ListView&mode=ajaxdelete&module=Reports');
	}

	/**
	 * To Change the Report to another folder

	 * @param $reportid -- The report id
	 * @param $folderid -- The folderid the which the report to be moved
	 */
	function changeFolder($reportid, $folderid) {
	global $adb;
	$imovereportsql = 'UPDATE vtiger_report SET folderid=? WHERE reportid=?';
	$adb->pquery($imovereportsql, array($folderid, $reportid));
	}
