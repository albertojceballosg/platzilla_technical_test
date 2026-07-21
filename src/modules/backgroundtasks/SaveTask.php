<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/backgroundtasks/lib/BackgroundTasksUtils.class.php');

	global $adb, $site_URL;
	setBugSnag ($site_URL);

	$dataSource = PlatzillaUtils::purify ($_POST, 'datasource');
	$trigger    = PlatzillaUtils::purify ($_POST, 'trigger');
	if ($trigger == BackgroundTask::TRIGGER_DAILY_SCHEDULE) {
		$dummy     = PlatzillaUtils::purify ($_POST, 'dailyfrequency');
		$dummy     = date_parse ($dummy);
		$frequency = ($dummy ['hour'] * 3600) + ($dummy ['minute'] * 60) + $dummy ['second'];
	} else if ($trigger == BackgroundTask::TRIGGER_TIMED_SCHEDULE) {
		$frequency = PlatzillaUtils::purify ($_POST, 'timedfrequency');
	} else {
		$frequency = null;
	}
	$taskData = array (
		'actions'      => PlatzillaUtils::purify ($_POST, 'actions'),
		'category'     => PlatzillaUtils::purify ($_POST, 'category'),
		'description'  => PlatzillaUtils::purify ($_POST, 'description'),
		'event'        => PlatzillaUtils::purify ($_POST, 'event'),
		'eventinstant' => PlatzillaUtils::purify ($_POST, 'eventinstant'),
		'filtergroups' => PlatzillaUtils::purify ($_POST, 'filtergroups'),
		'frequency'    => $frequency,
		'modulename'   => PlatzillaUtils::purify ($_POST, 'modulename'),
		'scope'        => PlatzillaUtils::purify ($_POST, 'scope'),
		'taskid'       => PlatzillaUtils::purify ($_POST, 'record'),
		'taskname'     => PlatzillaUtils::purify ($_POST, 'taskname'),
		'taskstatus'   => PlatzillaUtils::purify ($_POST, 'taskstatus'),
		'trigger'      => $trigger,
		'videourl'     => PlatzillaUtils::purify ($_POST,'taskvideo'),
	);
	try {
		$isInstance             = !empty ($_SESSION ['platInstancia']);
		$taskData ['protected'] = (!$isInstance) && ($taskData ['scope'] == BackgroundTask::SCOPE_USER);
		$task                   = BackgroundTasksUtils::saveTask ($adb, $taskData, $isInstance);

		if ($dataSource == 'wizard') {
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json');
			echo json_encode ('OK');
		} else {
			$_SESSION ['flashmessage'] = array (
				'iserror' => false,
				'message' => 'La tarea ha sido guardada',
			);
			header ('Location: index.php?module=backgroundtasks&action=ListView&parenttab=Settings');
		}
	} catch (Exception $e) {
		if ($dataSource == 'wizard') {
			header ('HTTP/1.1 400 Bad request');
			header ('Content-Type: application/json');
			echo json_encode ($e->getMessage ());
		} else {
			$_SESSION ['flashmessage'] = array (
				'iserror' => true,
				'message' => $e->getMessage (),
				'data'    => $taskData,
			);
			$recordUriPart             = !empty ($taskData ['taskid']) ? "&record={$taskData ['taskid']}" : '';
			header ("Location: index.php?module=backgroundtasks&action=EditView&parenttab=Settings{$recordUriPart}");
		}
	}
	exit ();
