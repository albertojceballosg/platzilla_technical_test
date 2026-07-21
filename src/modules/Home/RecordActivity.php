<?php
	require_once ('modules/Calendar/Activity.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	// Agregado por EB para integrar BUGSNAG - 20200330
	global $site_URL;
	require_once ('include/bugsnag-php-2.9.2/src/Bugsnag/Autoload.php');
	$bugsnag = new Bugsnag_Client('834d564193a48c47f138dc66d2cf5e83');
	$bugsnag->setAppVersion('1.0.0');
	if ($site_URL == 'https://apphome.platzillatest.com/') {
		$bugsnag->setReleaseStage('https://apphome.platzillatest.com/');
	} else if ($site_URL == 'https://app.platzilla.com/') {
		$bugsnag->setReleaseStage('https://app.platzilla.com/');
	} else {
		$bugsnag->setReleaseStage($site_URL);
	}
	$bugsnag->setErrorReportingLevel(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_WARNING & ~E_DEPRECATED);
	// Agregado por EB para integrar BUGSNAG - 20200330

	global $adb, $currentModule, $current_user;

	$activitiesData = PlatzillaUtils::purify ($_POST, 'activities');
	try {
		if (empty ($activitiesData)) {
			throw new Exception ('Debes suministrar al menos una actividad');
		}

		foreach ($activitiesData as $activityData) {
			$activity                                     = new Activity ();
			$activity->mode                               = 'create';
			$activity->column_fields ['assigned_user_id'] = $current_user->id;
			$activity->column_fields ['activitytype']     = 'Activity';
			$activity->column_fields ['date_start']       = $activityData ['startdate'];
			$activity->column_fields ['description']      = $activityData ['comment'];
			$activity->column_fields ['due_date']         = $activityData ['enddate'];
			$activity->column_fields ['eventstatus']      = 'Held';
			$activity->column_fields ['recurringtype']    = '--None--';
			$activity->column_fields ['subject']          = $activityData ['name'];
			$activity->column_fields ['time_start']       = $activityData ['starttime'];
			$activity->column_fields ['time_end']         = $activityData ['endtime'];
			$activity->column_fields ['visibility']       = 'Public';
			$activity->save ('Calendar');

			if (!empty ($activityData ['relatedcrmid'])) {
				$adb->pquery ('INSERT IGNORE INTO vtiger_seactivityrel (crmid, activityid) VALUES (?, ?)', array ($activityData ['relatedcrmid'], $activity->id));
			}
		}

		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json');
		echo json_encode ('OK');
	} catch (Exception $e) {
		header ('HTTP/1.1 400 Bad request');
		header ('Content-Type: application/json');
		echo json_encode ($e->getMessage ());
	}
	exit ();
