<?php
	require_once('include/logging.php');
	require_once('include/database/PearDatabase.php');
	$check=$_REQUEST['check'];
	global $default_charset;
	global $adb;
	$id='';
	if($_REQUEST['check']== 'reportCheck') {
	$reportName = $_REQUEST['reportName'];
	$sSQL='SELECT * FROM vtiger_report WHERE reportname=?';

	$sqlresult = $adb->pquery($sSQL, array(trim($reportName)));
	echo $adb->num_rows($sqlresult);
	} else if($_REQUEST['check']== 'folderCheck') {
	$folderName = function_exists('iconv') ? iconv('UTF-8',$default_charset, $_REQUEST['folderName']) : $_REQUEST['folderName'];
	$folderName =str_replace(array("'", '"'),'',$folderName);
	if($folderName == '' || !$folderName) {
		echo '999';
	} else {
		$SQL='SELECT * FROM vtiger_reportfolder WHERE foldername=?';
		$sqlresult = $adb->pquery($SQL, array(trim($folderName)));
		$id = $adb->query_result($sqlresult,0,'folderid');
		echo trim($adb->num_rows($sqlresult).'::'.$id);
	}
	}
