<?php
	require_once ('modules/Calendar/Activity.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $currentModule, $current_user;

	$data = PlatzillaUtils::purify ($_POST, 'data');
	if (empty ($data)) {
		return;
	}

	$today = date ('Y-m-d H:i:s');
	$date  = date ('Y-m-d');
	$hour  = date ('H:i:s');
	foreach ($data as $values) {
		$time = explode (':', $values [4]);

		$activity                                     = new Activity ();
		$activity->mode                               = 'create';
		$activity->column_fields ['assigned_user_id'] = $current_user->id;
		$activity->column_fields ['activitytype']     = 'Activity';
		$activity->column_fields ['date_start']       = $date;
		$activity->column_fields ['description']      = $values [1];
		$activity->column_fields ['due_date']         = $date;
		$activity->column_fields ['duration_hours']   = $time [0];
		$activity->column_fields ['duration_minutes'] = $time [1];
		$activity->column_fields ['eventstatus']      = 'Held';
		$activity->column_fields ['recurringtype']    = '--None--';
		$activity->column_fields ['subject']          = $values [0];
		$activity->column_fields ['time_start']       = $hour;
		$activity->column_fields ['time_end']         = $hour;
		$activity->column_fields ['visibility']       = 'Public';
		$activity->save ('Calendar');

		$adb->pquery ('INSERT IGNORE INTO vtiger_seactivityrel (crmid, activityid) VALUES (?, ?)', array ($values [3], $activity->id));
	}
	exit ();
