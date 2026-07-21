<?php
/*********************************************************************************
 * Endpoint AJAX para verificar las relaciones de una tarea antes de eliminarla
 ********************************************************************************/

require_once('include/database/PearDatabase.php');
require_once('modules/orden_de_trabajo/handlers/taskToWork.class.php');

global $adb;

$activityId = isset($_REQUEST['activityid']) ? (int)$_REQUEST['activityid'] : 0;
$workId = isset($_REQUEST['workid']) ? (int)$_REQUEST['workid'] : 0;

if (empty($activityId) || empty($workId)) {
	echo json_encode(array(
		'success' => false,
		'message' => 'Parámetros inválidos'
	));
	exit;
}

$taskHandler = taskToWork::getInstance($adb);
$result = $taskHandler->checkTaskRelations($activityId, $workId);

echo json_encode(array(
	'success' => true,
	'data' => $result
));
