<?php
	require_once('include/home.php');
	global $adb, $log;

	$reportid = vtlib_purify($_REQUEST['reportid']);
	$windowtitle = vtlib_purify($_REQUEST['windowtitle']);
	$charttype = vtlib_purify($_REQUEST['charttype']);

	$homeObj = new Homestuff();
	$homeObj->stufftitle = $windowtitle;
	$homeObj->stufftype = 'ReportCharts';
	$homeObj->selreport = $reportid;
	$homeObj->selreportcharttype = $charttype;
	$homeObj->addStuff();
