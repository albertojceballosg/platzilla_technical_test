<?php
	require_once('Smarty_setup.php');
	require_once('modules/Reports/ReportRun.php');
	require_once('modules/Reports/Reports.php');

	global $app_strings;
	global $mod_strings;
	$oPrint_smarty = new vtigerCRM_Smarty;
	$reportid = vtlib_purify($_REQUEST['record']);
	$oReport = new Reports($reportid);
	$filtercolumn = $_REQUEST['stdDateFilterField'];
	$filter = $_REQUEST['stdDateFilter'];
	$oReportRun = new ReportRun($reportid);

	// Convert the user date format to DB date format
	$startdate = DateTimeField::convertToDBFormat($_REQUEST['startdate']);
	// Convert the user date format to DB date format
	$enddate = DateTimeField::convertToDBFormat($_REQUEST['enddate']);
	$filterlist = $oReportRun->runTimeFilter($filtercolumn,$filter,$startdate,$enddate);

	$arr_values = $oReportRun->generateReport('PRINT',$filterlist);
	$total_report = $oReportRun->generateReport('PRINT_TOTAL',$filterlist);
	$oPrint_smarty->assign('COUNT',$arr_values[1]);
	$oPrint_smarty->assign('APP',$app_strings);
	$oPrint_smarty->assign('MOD',$mod_strings);
	$oPrint_smarty->assign('REPORT_NAME',$oReport->reportname);
	$oPrint_smarty->assign('PRINT_CONTENTS',$arr_values[0]);
	$oPrint_smarty->assign('TOTAL_HTML',$total_report);
	$oPrint_smarty->display('PrintReport.tpl');
