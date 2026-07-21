<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Reports/lib/ReportUtils.class.php');

	global $adb;

	$reportId = PlatzillaUtils::purify ($_GET, 'record');

	$data = ReportUtils::getReportById ($adb, $reportId);

	header ('HTTP/1.1 200 OK');
	header ('Content-Type: application/json');
	echo json_encode ($data);
	exit ();
