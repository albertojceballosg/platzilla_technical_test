<?php
	ini_set('max_execution_time','1800');
	require_once('modules/Reports/ReportRun.php');
	require_once('modules/Reports/Reports.php');
	require('include/tcpdf/tcpdf.php');
	$language = $_SESSION['authenticated_user_language'].'.lang.php';
	require_once("include/language/$language");
	$reportid = vtlib_purify($_REQUEST['record']);
	$oReport = new Reports($reportid);
	$filtercolumn = $_REQUEST['stdDateFilterField'];
	$filter = $_REQUEST['stdDateFilter'];
	$oReportRun = new ReportRun($reportid);

	$startdate = ($_REQUEST['startdate']);
	$enddate = ($_REQUEST['enddate']);
	if(!empty($startdate) && !empty($enddate) && $startdate != '0000-00-00' && $enddate != '0000-00-00') {
	$date = new DateTimeField($_REQUEST['startdate']);
	$endDate = new DateTimeField($_REQUEST['enddate']);
	// Convert the user date format to DB date format
	$startdate = $date->getDBInsertDateValue();
	// Convert the user date format to DB date format
	$enddate = $endDate->getDBInsertDateValue();
	}

	$filterlist = $oReportRun->runTimeFilter($filtercolumn,$filter,$startdate,$enddate);

	$pdf = $oReportRun->getReportPdf($filterlist);
	$pdf->Output('Reports.pdf','D');

	exit();
