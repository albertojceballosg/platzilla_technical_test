<?php
	require_once ('include/platzilla/Data/ActivityReportManager.php');
	require_once ('include/utils/NumberHelper.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Calendar/Activity.php');
	require_once ('modules/Calendar/lib/CalendarHelper.class.php');
	
	global $adb, $currentModule, $current_user;


	$activityMode     = PlatzillaUtils::purify ($_POST, 'activity_mode');
	$activityType     = PlatzillaUtils::purify ($_POST, 'activitytype');
	$description      = PlatzillaUtils::purify ($_POST, 'description');
	$endDate          = PlatzillaUtils::purify ($_POST, 'due_date');
	$endTime          = PlatzillaUtils::purify ($_POST, 'time_end');
	$estimatedTime    = PlatzillaUtils::purify ($_POST, 'estimated_time');
	$estimatedCost    = PlatzillaUtils::purify ($_POST, 'estimated_cost');
	$estimatedTimeUnit = PlatzillaUtils::purify ($_POST, 'estimated_time_unit');
	$relatedTab       = PlatzillaUtils::purify ($_POST, 'formodule', null);
	$eventStatus      = PlatzillaUtils::purify ($_POST, 'eventstatus');
	$ownerGroupId     = PlatzillaUtils::purify ($_POST, 'assigned_group_id', $current_user->id);
	$ownerType        = PlatzillaUtils::purify ($_POST, 'assigntype');
	$ownerUserId      = PlatzillaUtils::purify ($_POST, 'assigned_user_id');
	$location         = PlatzillaUtils::purify ($_POST, 'location');
	$mode             = PlatzillaUtils::purify ($_POST, 'mode', 'create');
	$pageNumber       = PlatzillaUtils::purify ($_POST, 'pagenumber', PlatzillaUtils::purify ($_POST, 'start'));
	$parentTab        = PlatzillaUtils::purify ($_POST, 'parenttab');
	$priority         = PlatzillaUtils::purify ($_POST, 'taskpriority');
	$plannedTask      = PlatzillaUtils::purify ($_POST, 'planned_task');
	$progress         = PlatzillaUtils::purify ($_POST, 'progress');
	$record           = PlatzillaUtils::purify ($_POST, 'record');
	$relatedEntityIds = PlatzillaUtils::purify ($_POST, 'relatedcrmids');
	$returnAction     = PlatzillaUtils::purify ($_POST, 'return_action', 'DetailView');
	$returnEntityId   = PlatzillaUtils::purify ($_POST, 'return_id');
	$returnModule     = PlatzillaUtils::purify ($_POST, 'return_module', $currentModule);
	$returnViewName   = PlatzillaUtils::purify ($_POST, 'return_viewname', 0);
	$searchUrl        = PlatzillaUtils::purify ($_POST, 'search_url');
	$showInMatrix     = PlatzillaUtils::purify ($_POST, 'show_in_matrix', 'NO');
	$startDate        = PlatzillaUtils::purify ($_POST, 'date_start');
	$startTime        = PlatzillaUtils::purify ($_POST, 'time_start');
	$subject          = PlatzillaUtils::purify ($_POST, 'subject');
	$returnTab        = PlatzillaUtils::purify ($_POST, 'tab', null);
	$visibility       = PlatzillaUtils::purify ($_POST, 'visibility');
	$function         = PlatzillaUtils::purify ($_POST, 'function', null);
	$categoryId       = PlatzillaUtils::purify ($_POST, 'categoryid', null);
	$categoryName     = PlatzillaUtils::purify ($_POST, 'category_name', null);
	$importance       = PlatzillaUtils::purify ($_POST, 'taskImport');
	
	$relatedEntityIds = is_array ($relatedEntityIds) ? $relatedEntityIds : array($relatedEntityIds);
	
	// Si relatedcrmids está vacío, intentar obtener el ID relacionado desde parent_id o contact_id
	// (parámetros enviados desde la pestaña Acciones de otros módulos via GET)
	if (empty($relatedEntityIds) || (count($relatedEntityIds) == 1 && empty($relatedEntityIds[0]))) {
		$parentId = PlatzillaUtils::purify ($_REQUEST, 'parent_id');
		$contactId = PlatzillaUtils::purify ($_REQUEST, 'contact_id');
		
		if (!empty($parentId)) {
			$relatedEntityIds = array($parentId);
		} elseif (!empty($contactId)) {
			$relatedEntityIds = array($contactId);
		}
	}
	
	// Si relatedTab no está definido, obtenerlo desde return_module
	if (empty($relatedTab) && !empty($returnModule)) {
		$relatedTab = $returnModule;
	}
	
	if ((is_numeric ($categoryId)) && !empty ($categoryName)) {
		$userCategories = array ($ownerUserId);
		if ($ownerUserId != $current_user->id) {
			$userCategories [] = $current_user->id;
		}
		foreach ($userCategories as $userId) {
			$isCategory = CalendarHelper::checkTaskCategory ($adb, $userId, $categoryName);
			if (!$isCategory) {
				$categoryId = CalendarHelper::saveTaskCategory ($adb, $categoryName, $userId, $categoryId);
			}
		}
	}
	$activity = new Activity ();
	if (!empty ($record)) {
		$activity->id = $record;
	}
	if (!empty ($mode)) {
		$activity->mode = $mode;
		if ($mode == 'edit') {
			$numberingFormat = NumberHelper::getInstance ($adb);
			$numberingFormat->setUserNumberingFormat ($current_user, true);
			$entity = new Activity ();
			$entity->retrieve_entity_info ($record, 'Calendar');
			$estimatedTime = (empty($estimatedTime)) ? $entity->column_fields ['estimated_time'] : $estimatedTime;
			if (
				empty ($entity->column_fields ['due_date']) &&
				empty ($endDate) &&
				$entity->column_fields ['progress'] >= 100
			) {
				$endDate = date ('Y-m-d');
			}
			if (
				($entity->column_fields ['progress'] < 100) &&
				($entity->column_fields ['eventstatus'] != 'Held')
			) {
				if ($eventStatus == 'Postponed') {
					$activityReport = ActivityReport::getInstance ()
						->setId (null)
						->setActivityId ($record)
						->setProgress (floatval ($entity->column_fields ['progress']))
						->setReport ('La tarea ha sido pospuesta')
						->setTimeDuration (0.0)
						->setTitle ('Se pospone la realización de la tarea')
						->setUserId ($ownerUserId);
					ActivityReportManager::getInstance ($adb)->saveActivityReport ($activityReport);
				} else if ($entity->column_fields ['progress'] > 0){
					$eventStatus = 'Not Held';
				}
			}
			
			if (empty($entity->column_fields ['date_start']) && empty ($startDate)) {
				$startDate = date ('Y-m-d');
			} else if (!empty($entity->column_fields ['date_start']) && empty ($startDate)) {
				$startDate = $entity->column_fields ['date_start'];
			}
			
		} else {
			if (intval ($progress) >= 100) {
				$eventStatus = 'Held';
			}
			if ($eventStatus == 'Held' && empty($endDate)) {
				$endDate = date('Y-m-d');
			}
		}
	}
	if (($function == 'TASK_FROM_MODULE') && empty($estimatedTime)) {
		if ($activityType == 'Meeting') {
			$estimatedTime = 0.75;
		}else if($activityType == 'Call') {
			$estimatedTime = 0.10;
		} else {
			$estimatedTime = 0.50;
		}
	}
	
	if (($function == 'TASK_FROM_MODULE') && empty($startTime)) {
			if ($activityType == 'Meeting') {
				$startTime = '09:00:00';
			}else if($activityType == 'Call') {
				$startTime = '09:00:00';
			} else {
				$startTime = '00:00:00';
			}
		}
	
	$activity->column_fields ['activitytype']     = $activityType;
	$activity->column_fields ['assigned_user_id'] = $ownerType == 'T' ? $ownerGroupId : $ownerUserId;
	$activity->column_fields ['date_start']       = $startDate;
	$activity->column_fields ['description']      = $description;
	$activity->column_fields ['due_date']         = $endDate;
	$activity->column_fields ['estimated_time']   = $estimatedTime;
	$activity->column_fields ['estimated_cost']   = $estimatedCost;
	$activity->column_fields ['estimated_time_unit'] = $estimatedTimeUnit;
	$activity->column_fields ['progress_condition'] = 'No iniciada';
	$activity->column_fields ['combined_condition'] = 'PICK_ACTIVITY_ON_TIME_ON_BUDGET';
	$activity->column_fields ['progress_weighting_factor'] = 0;
	$activity->column_fields ['eventstatus']      = $eventStatus;
	$activity->column_fields ['location']         = $location;
	$activity->column_fields ['notime']           = 0;
	$activity->column_fields ['progress']         = $progress;
	$activity->column_fields ['recurringtype']    = '--None--';
	$activity->column_fields ['sendnotification'] = 0;
	$activity->column_fields ['subject']          = $subject;
	$activity->column_fields ['taskpriority']     = $priority;
	$activity->column_fields ['categoryid']       = (!empty($categoryId)) ? $categoryId : 10;
	$activity->column_fields ['time_end']         = !empty ($endDate) ? $endTime : null;
	$activity->column_fields ['time_start']       = $startTime;
	$activity->column_fields ['visibility']       = !empty ($visibility) ? $visibility : 'Public';
	$activity->column_fields ['importance']       = $importance;
	$activity->column_fields ['related_id']       = !empty($relatedEntityIds[0]) ? $relatedEntityIds[0] : null;
	$activity->column_fields ['planned_task']     = $plannedTask;
	$activity->column_fields ['show_in_matrix']   = $showInMatrix;
	$activity->column_fields ['related_to']       = $relatedTab;
	
	$activity->save ('Calendar');
	if ($mode == 'edit') {
		$numberingFormat->setUserNumberingFormat ($current_user);
	}
	
	// Formatear datos para la vista de tareas
	require_once('include/utils/NumberHelper.class.php');
	$numberingHelper = NumberHelper::getInstance($adb, $current_user);
	
	$formattedEstimatedTime = $numberingHelper->setNumberFormat($estimatedTime, 'estimated_time');
	$formattedEstimatedCost = $numberingHelper->setNumberFormat($estimatedCost, 'estimated_cost');
	
	if (empty ($function) || ($mode == 'edit'  && !empty ($relatedEntityIds))) {
		$adb->pquery ('DELETE FROM vtiger_seactivityrel WHERE activityid=?', array($activity->id));
	}
	if (!empty ($relatedEntityIds)) {
		foreach ($relatedEntityIds as $relatedEntityId) {
				$adb->pquery ('INSERT IGNORE INTO vtiger_seactivityrel (crmid, activityid) VALUES (?, ?)', array($relatedEntityId, $activity->id));
		}
	}
	if (empty ($returnEntityId)) {
		$returnEntityId = $activity->id;
	}
	
	if ($function == 'TASK_FROM_MODULE') {
		try {
			if (empty($activity)) {
				throw new Exception ('Actividad no encontrada');
			}
			$newEvent =  array (
			      'id'              => $activity->id,
			      'crmid'           => $relatedEntityIds[0],
			      'backgroundColor' =>'#ffffe5',
			      'borderColor'     => '#000000',
			      'end'             => (!empty(($endDate))) ? $endDate : null,
			      'start'           => $startDate,
			      'progress'        => 0,
			      'textColor'       =>  '#000000',
			      'title'           =>  $subject,
			      'url'             => 'index.php?module=' . $relatedTab. '&action=DetailView&record=' . $relatedEntityIds[0],
			);
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array ('error' => 'OK', 'activityId' => $activity->id, 'categoryId' => $categoryId, 'event' => $newEvent));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => $e->getMessage ()));
		}
		exit();
	}
	
	// Respuesta AJAX para operaciones desde la vista de detalle de tareas
	if (!empty($_POST['ajax']) || $function == 'TASK_FROM_MODULE') {
		header ('Access-Control-Allow-Origin: *');
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json; charset=utf-8');
		
		$response = array (
			'error' => 'OK',
			'activityId' => $activity->id,
			'categoryId' => $categoryId,
			'formatted_estimated_time' => $formattedEstimatedTime,
			'formatted_estimated_cost' => $formattedEstimatedCost,
			'estimated_time' => $estimatedTime,
			'estimated_cost' => $estimatedCost
		);
		
		if ($function == 'TASK_FROM_MODULE') {
			$response['event'] = $newEvent;
		}
		
		echo json_encode($response);
		exit();
	}
	
	$activityModeUrlPart = !empty ($activityMode) ? "&activity_mode={$activityMode}" : '';
	$pageNumberUriPart   = !empty ($pageNumber) ? "&start={$pageNumber}" : '';
	$parentTabUrlPart    = !empty ($parentTab) ? "&parenttab={$parentTab}" : '';
	$recordIdUrlPart     = !empty ($returnEntityId) ? "&record={$returnEntityId}" : '';
	$searchUrlPart       = !empty ($searchUrl) ? "&search_url={$returnEntityId}" : '';
	$viewNameUrlPart     = !empty ($returnViewName) ? "&viewname={$returnViewName}" : '';
	$returnAction       .= !empty ($returnTab) ? "&tab={$returnTab}" : '';

	header ("Location: index.php?module={$returnModule}&action={$returnAction}{$activityModeUrlPart}{$recordIdUrlPart}{$viewNameUrlPart}{$pageNumberUriPart}{$searchUrlPart}{$parentTabUrlPart}");
	exit ();
