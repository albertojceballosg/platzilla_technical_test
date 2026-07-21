<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Calendar/Calendar.php');

	global $adb, $current_user, $currentModule;
	
	$activityType    = PlatzillaUtils::purify ($_POST, 'activitytype');
	$assignType      = PlatzillaUtils::purify ($_POST, 'assigntype');
	$assignedGroupId = PlatzillaUtils::purify ($_POST, 'assigned_group_id');
	$assignedUserId  = PlatzillaUtils::purify ($_POST, 'assigned_user_id');
	$categoryid      = PlatzillaUtils::purify ($_POST,'categoryid', 10);
	$description     = PlatzillaUtils::purify ($_POST, 'description');
	$endDate         = PlatzillaUtils::purify ($_POST, 'enddate');
	$endTime         = PlatzillaUtils::purify ($_POST, 'endtime');
	$eventStatus     = PlatzillaUtils::purify ($_POST, 'eventstatus');
	$location        = PlatzillaUtils::purify ($_POST, 'location');
	$invitedUserIds  = PlatzillaUtils::purify ($_POST, 'inviteduserids');
	$mode            = PlatzillaUtils::purify ($_POST, 'mode', null);
	$progress        = PlatzillaUtils::purify ($_POST, 'progress', null);
	$record          = PlatzillaUtils::purify ($_POST, 'taskid', 0);
	$relatedCrmIds   = PlatzillaUtils::purify ($_POST, 'relatedcrmids');
	$startDate       = PlatzillaUtils::purify ($_POST, 'startdate');
	$startTime       = PlatzillaUtils::purify ($_POST, 'starttime');
	$subject         = PlatzillaUtils::purify ($_POST, 'subject');
	$taskImport      = PlatzillaUtils::purify ($_POST, 'taskImport');
	$taskPriority    = PlatzillaUtils::purify ($_POST, 'taskpriority', 'Low');
	$visibility      = PlatzillaUtils::purify ($_POST, 'visibility');
	
	$priorityTranslate = array ('High' => 'Alto', 'Low' => 'Bajo');
	
	try {
		/** @var Calendar|CRMEntity|stdClass $focus */
		$focus                                     = CRMEntity::getInstance ('Calendar');
		$focus->column_fields ['activitytype']     = $activityType;
		$focus->column_fields ['assigned_user_id'] = $assignType == 'U' ? $assignedUserId : $assignedGroupId;
		$focus->column_fields ['date_start']       = $startDate;
		$focus->column_fields ['description']      = $description;
		$focus->column_fields ['due_date']         = $endDate;
		$focus->column_fields ['eventstatus']      = $eventStatus;
		$focus->column_fields ['location']         = $location;
		$focus->column_fields ['taskpriority']     = $priorityTranslate[ $taskPriority ];
		$focus->column_fields ['progress']         = $eventStatus == 'Held' ? 100 : $progress;
		$focus->column_fields ['recurringtype']    = '--None--';
		$focus->column_fields ['subject']          = $subject;
		$focus->column_fields ['time_end']         = $endTime;
		$focus->column_fields ['time_start']       = $startTime;
		$focus->column_fields ['visibility']       = $visibility;
		$focus->column_fields ['categoryid']       = $categoryid;
		$focus->column_fields ['importance']       = $taskImport;
		$focus->column_fields ['related_id']       = $relatedCrmIds[0];
		if (!empty ($record) && $record != 'null') {
			$focus->id = $record;
		}
		if (!empty ($mode)) {
			$focus->mode = $mode;
		}
		$focus->save ('Calendar');
		if (empty ($focus->id)) {
			throw new Exception ('Se ha presentado un error al guardar la actividad');
		}
		if (!empty ($mode && $mode == 'edit')) {
			$adb->pquery ('DELETE FROM vtiger_seactivityrel WHERE activityid=?', array($focus->id));
			$adb->pquery ('DELETE FROM vtiger_invitees WHERE activityid=?', array($focus->id));
			$adb->pquery ('DELETE FROM vtiger_salesmanactivityrel WHERE activityid=?', array($focus->id));
		}
		
		$result = $adb->pquery('SELECT activityid FROM vtiger_salesmanactivityrel WHERE activityid=?', array($focus->id));
		if (!$adb->num_rows ($result)) {
			$adb->pquery ('INSERT INTO vtiger_salesmanactivityrel (smid, activityid) VALUES (?, ?)', array ($current_user->id, $focus->id));
		}

		if (!empty ($relatedCrmIds)) {
			foreach ($relatedCrmIds as $relatedCrmId) {
				$adb->pquery ('INSERT INTO vtiger_seactivityrel (crmid, activityid) VALUES (?, ?)', array ($relatedCrmId, $focus->id));
			}
		}

		$adb->pquery (
			'INSERT INTO vtiger_audit_trial (sessionid, userid, module, action, recordid, actiondate) VALUES (?, ?, ?, ?, ?, ?)',
			array (session_id (), $current_user->id, $currentModule, 'DetailView', $focus->id, $adb->formatDate (date ('Y-m-d H:i:s'), true))
		);

		foreach ($invitedUserIds as $userId) {
			if (!empty ($userId)) {
				$adb->pquery ('INSERT INTO vtiger_invitees (activityid, inviteeid) VALUES (?, ?)', array ($focus->id, $userId));
			}
		}
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json');
		echo json_encode ('OK');
	} catch (Exception $e) {
		header ('HTTP/1.1 400 Baad request');
		header ('Content-Type: application/json');
		echo json_encode ($e->getMessage ());
	}
	exit ();
