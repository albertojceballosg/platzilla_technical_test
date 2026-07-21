<?php
	require_once('modules/Reports/Reports.php');
	require_once('include/logging.php');
	require_once('include/database/PearDatabase.php');
	global $current_user;

	$local_user = clone $current_user;
	require('user_privileges/user_privileges.php');
	global $current_user,$adb,$is_admin;

	if(isset($_REQUEST['idlist']) && $_REQUEST['idlist']!= '') {
	$id_array = array();
	$id_array = explode(':',$_REQUEST['idlist']);

	$query = $adb->pquery("SELECT userid FROM vtiger_user2role INNER JOIN vtiger_users on vtiger_users.id=vtiger_user2role.userid inner join vtiger_role on vtiger_role.roleid=vtiger_user2role.roleid WHERE vtiger_role.parentrole LIKE '".$current_user_parent_role_seq."::%'", array());
	$subordinate_users = array();
	$numRowsQuery = $adb->num_rows($query);
	for($i=0; $i < $numRowsQuery; $i++){
		$subordinate_users[] = $adb->query_result($query,$i,'userid');
	}

	$countIdArray = (count($id_array)-1);
	for($i=0; $i<$countIdArray; $i++)
	{
		$own_query = $adb->pquery('SELECT reportname,owner FROM vtiger_report WHERE reportid=?',array($id_array[$i]));
		$owner = $adb->query_result($own_query,0,'owner');
		if($is_admin==true || in_array($owner,$subordinate_users) || $owner==$current_user->id) {
			deleteReport($id_array[$i]);
		} else {
			$del_failed []= $adb->query_result($own_query,0,'reportname');
		}
	}

	if(!empty($del_failed)) {
		header('Location: index.php?action=ReportsAjax&file=ListView&mode=ajax&module=Reports&del_denied=' . implode(',', $del_failed));
	} else {
		header('Location: index.php?action=ReportsAjax&file=ListView&mode=ajax&module=Reports');
	}
	} else if(isset($_REQUEST['record']) && $_REQUEST['record']!= '') {
		$id  = vtlib_purify($_REQUEST['record']);
		if (isset ($_GET['tab']) && $_GET['tab']!= '') {
			deleteReport ($id);
			exit();
		} else if (isset ($_REQUEST ['from']) && $_REQUEST ['from'] == 'metrics') {
			deleteReport ($id);
			echo 'OK';
		} else {
			deleteReport ($id);
			header('Location: index.php?action=ReportsAjax&file=ListView&mode=ajaxdelete&module=Reports');
		}
	}

	/**
	 * To Delete a Report

	 * @param $reportid -- The report id
	 */
	function deleteReport($reportid) {
	global $adb;
	$idelreportsql = 'DELETE FROM vtiger_selectquery WHERE queryid=?';
	$adb->pquery($idelreportsql, array($reportid));

	$ireportsql = 'DELETE FROM vtiger_report WHERE reportid=?';
	$adb->pquery($ireportsql, array($reportid));

	$reportsql = 'DELETE FROM vtiger_scheduled_reports WHERE reportid=?';
	$adb->pquery($reportsql, array($reportid));
	}
