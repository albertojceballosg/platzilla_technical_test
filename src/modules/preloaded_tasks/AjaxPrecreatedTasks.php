<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/preloaded_tasks/lib/PrecreatedTaskUtils.class.php');
	
	global $adb, $currentModule, $mod_strings, $theme, $site_URL;
	
	setBugSnag ($site_URL);
	
	$function   = PlatzillaUtils::purify ($_REQUEST, 'function');
	$moduleName = PlatzillaUtils::purify ($_REQUEST, 'flmodule');
	$isInstance = !empty ($_SESSION ['platInstancia']);
	
	if ($function == 'CHANGE-STATUS-TASK') {
		try {
			$record = PlatzillaUtils::purify ($_POST, 'record');
			$status = PlatzillaUtils::purify ($_POST, 'status');
			
			if (empty ($record)) {
				throw new Exception ('Uoops! Imposible encontar la tarea.');
			}
			if (empty ($status)) {
				throw new Exception ('Cambio a un estatus no definido.');
			}
			
			if ($status == PrecreatedTaskInterface::PRECRATED_TASK_DISABLED) {
				$status  = PrecreatedTaskInterface::PRECRATED_TASK_ENABLED;
				$infoBtn = 'Desactivar tarea';
				$faBtn   = 'fa-check-square-o';
			} else if ($status == PrecreatedTaskInterface::PRECRATED_TASK_ENABLED) {
				$status = PrecreatedTaskInterface::PRECRATED_TASK_DISABLED;
				$infoBtn = 'Activar tarea';
				$faBtn   = 'fa-square-o';
			} else {
				throw new Exception ('Cambio a un estatus no definido.');
			}
			
			$preCreatedTask   = new PrecreatedTaskUtils ();
			$preCreatedTask->upDatePreCreateTaskStatus ($status, $record);
			$smarty = new vtigerCRM_Smarty ();
			
			$htmlOutput = array ($status, $mod_strings [$status], $infoBtn, $faBtn);
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $htmlOutput));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else if ($function == 'CHANGE-STATUS-AREA') {
		try {
			$record = PlatzillaUtils::purify ($_POST, 'record');
			$status = PlatzillaUtils::purify ($_POST, 'status');
			
			if (empty ($record)) {
				throw new Exception ('Uoops! Imposible encontar el área.');
			}
			if (empty ($status)) {
				throw new Exception ('Cambio a un estatus no definido.');
			}
			
			if ($status == PrecreatedTaskInterface::PRECRATED_TASK_DISABLED) {
				$status  = PrecreatedTaskInterface::PRECRATED_TASK_ENABLED;
				$infoBtn = 'Desactivar área';
				$faBtn   = 'fa-check-square-o';
			} else if ($status == PrecreatedTaskInterface::PRECRATED_TASK_ENABLED) {
				$status = PrecreatedTaskInterface::PRECRATED_TASK_DISABLED;
				$infoBtn = 'Activar area';
				$faBtn   = 'fa-square-o';
			} else {
				throw new Exception ('Cambio a un estatus no definido.');
			}
			
			$preCreatedTask   = new PrecreatedTaskUtils ();
			$preCreatedTask->upDateAreaStatus ($status, $record);
			$smarty = new vtigerCRM_Smarty ();
			
			$htmlOutput = array ($status, $mod_strings [$status], $infoBtn, $faBtn);
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $htmlOutput));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else if ($function == 'DELETE-TASK') {
		try {
			$record = PlatzillaUtils::purify ($_POST, 'record');
			
			if (empty ($record)) {
				throw new Exception ('Uoops! Imposible encontar tarea.');
			}
			
			
			$preCreatedTask   = new PrecreatedTaskUtils ();
			$preCreatedTask->deletePrecreatedTask ($record);
			$smarty = new vtigerCRM_Smarty ();
			
			$htmlOutput = '';
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $htmlOutput));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	}
