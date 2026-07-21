<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/preloaded_tasks/lib/PrecreatedTaskUtils.class.php');

	global $adb, $app_strings, $current_user, $mod_strings, $theme, $site_URL;
	setBugSnag ($site_URL);

	$smarty = new vtigerCRM_Smarty ();
	
	$codeArea        = PlatzillaUtils::purify ($_POST, 'area');
	$record          = PlatzillaUtils::purify ($_REQUEST, 'record', null);
	$status          = PlatzillaUtils::purify ($_POST, 'status_view');
	$tab             = PlatzillaUtils::purify ($_POST, 'tab', null);
	$tabName         = PlatzillaUtils::purify ($_POST, 'tabname');
	$taskName        = PlatzillaUtils::purify ($_POST, 'task_title');
	$taskDescription = PlatzillaUtils::purify ($_POST, 'task_descripcion');
	
	try {
		if ((!empty ($_SESSION ['platInstancia'])) || ($current_user->is_admin != 'on')) {
			throw new Exception ('Solo el usuario administrador de la plataforma madre, puede actulizar las Tareas predefinidas');
		}
		if (empty ($codeArea)) {
			throw new Exception ('Área no seleccionada!');
		}
		if (empty ($tabName)) {
			throw new Exception ('Módulo no definido');
		}
		if (empty ($taskName)) {
			throw new Exception ('Título de la descripción es requerido!');
		}
		
		if (empty ($status)) {
			throw new Exception ('Seleccionar el estatus de la tarea');
		}
		
		$preCreatedTask   = new PrecreatedTaskUtils ();
		$preCreatedTask->savePreCreatedTask (
			PrecreatedTask::getInstance ()
				->setId ($record)
				->setStatus ($status)
				->setCodeArea ($codeArea)
				->setTabName ($tabName)
				->setTaskName ($taskName)
				->setTaskDescription ($taskDescription)
		);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => (!empty($record)) ? 'Se ha actualizado la Tarea' : 'Se ha creado la Tarea!',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	header ("Location: index.php?module=preloaded_tasks&action=ListView&tab={$tab}");
