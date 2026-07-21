<?php
	require_once ('modules/Calendar/Activity.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $currentModule;

	$activityId   = PlatzillaUtils::purify ($_POST, 'record');
	$returnAction = PlatzillaUtils::purify ($_POST, 'return_action', 'ListView');
	$returnModule = PlatzillaUtils::purify ($_POST, 'return_module', 'Calendar');
	$function     = PlatzillaUtils::purify ($_POST, 'function', null);
	$progress     = PlatzillaUtils::purify ($_POST, 'progress', 100);
	$status       = PlatzillaUtils::purify ($_POST, 'eventstatus', 'Held');

	try {
		$entity = new Activity ();
		$entity->retrieve_entity_info ($activityId, 'Calendar');
		$entity->id                              = $activityId;
		$entity->mode                            = 'edit';
		$entity->column_fields ['eventstatus'] = $status;
		if (empty ($entity->column_fields ['due_date'])) {
			$entity->column_fields ['due_date'] = date ('Y-m-d');
		}
		$entity->save ('Calendar');
		
		if ($function == 'TASK_FROM_MODULE') {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array ('error' => 'OK'));
			exit();
		}
		
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'La tarea ha sido realizada',
		);
	} catch (Exception $e) {
		if ($function == 'TASK_FROM_MODULE') {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => $e->getMessage ()));
		}
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	header ("Location: index.php?module={$returnModule}&action={$returnAction}");
	exit ();
