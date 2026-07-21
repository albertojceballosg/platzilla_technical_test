<?php


include_once("include/utils/utils.php");
include_once('include/utils/comunesTareas.php');
	
global $conex;
global $adb;

if($_REQUEST['chrono']=='true' && $_REQUEST['time']!='' && $_REQUEST['record']!=''){
	$sql="update vtiger_activity set cronometro='".$_REQUEST['time']."' where activityid='".$_REQUEST['record']."'";
	if($adb->query($sql))
		die("SUCCESS");
	die("FAIL");
}
die();
?>