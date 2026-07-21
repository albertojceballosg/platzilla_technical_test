<?php
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $currentModule;

	$activityId = PlatzillaUtils::purify ($_POST, 'activityid');
	$endDate    = PlatzillaUtils::purify ($_POST, 'enddate');
	$endTime    = PlatzillaUtils::purify ($_POST, 'endtime');
	$startDate  = PlatzillaUtils::purify ($_POST, 'startdate');
	$startTime  = PlatzillaUtils::purify ($_POST, 'starttime');

	$adb->pquery (
		'UPDATE vtiger_activity SET date_start=?, due_date=?, time_start=?, time_end=? WHERE activityid=?',
		array ($startDate, $endDate, $startTime, $endTime, $activityId)
	);
	exit ();
