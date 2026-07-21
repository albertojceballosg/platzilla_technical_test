<?php
	global $php_max_execution_time;
	set_time_limit($php_max_execution_time);
	global $tmp_dir;
	global $root_directory;
	$filter = '';

	require_once('include/php_writeexcel/class.writeexcel_workbook.inc.php');
	require_once('include/php_writeexcel/class.writeexcel_worksheet.inc.php');
	require_once('modules/Reports/ReportRun.php');
	require_once('modules/Reports/Reports.php');

	$fname = tempnam($root_directory.$tmp_dir, 'merge2.xls');

	// Write out the data
	$reportid = vtlib_purify($_REQUEST['record']);
	$oReport = new Reports($reportid);
	$filtercolumn = $_REQUEST['stdDateFilterField'];
	$startdate = ($_REQUEST['startdate']);
	$enddate = ($_REQUEST['enddate']);
	if(!empty($startdate) && !empty($enddate) && $startdate != '0000-00-00' && $enddate != '0000-00-00') {
	$filter = $_REQUEST['stdDateFilter'];
	$date = new DateTimeField($_REQUEST['startdate']);
	$endDate = new DateTimeField($_REQUEST['enddate']);
	// Convert the user date format to DB date format
	$startdate = $date->getDBInsertDateValue();
	// Convert the user date format to DB date format
	$enddate = $endDate->getDBInsertDateValue();
	}
	$oReportRun = new ReportRun($reportid);
	$filterlist = $oReportRun->runTimeFilter($filtercolumn,$filter,$startdate,$enddate);

	$oReportRun->writeReportToExcelFile($fname, $filterlist);

	if(isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'],'MSIE')) {
	header('Pragma: public');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	}
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename="Reports.xls"');
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	header('Content-Length: ' . filesize($fname));
	ob_clean();
	flush();
	readfile($fname);
