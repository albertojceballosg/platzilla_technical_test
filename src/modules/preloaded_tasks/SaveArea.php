<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/preloaded_tasks/lib/PrecreatedTaskUtils.class.php');

	global $adb, $app_strings, $current_user, $mod_strings, $theme, $site_URL;
	setBugSnag ($site_URL);

	$smarty = new vtigerCRM_Smarty ();
	
	$codeArea        = PlatzillaUtils::purify ($_POST, 'code_area');
	$record          = PlatzillaUtils::purify ($_REQUEST, 'record', null);
	$status          = PlatzillaUtils::purify ($_POST, 'status_view');
	$tab             = PlatzillaUtils::purify ($_POST, 'tab', null);
	$areaName        = PlatzillaUtils::purify ($_POST, 'name_area');
	
	try {
		if ((!empty ($_SESSION ['platInstancia'])) || ($current_user->is_admin != 'on')) {
			throw new Exception ('Solo el usuario administrador de la plataforma madre, puede actulizar las Tareas predefinidas');
		}
		$preCreatedTask   = new PrecreatedTaskUtils ();
		if (empty ($codeArea)) {
			throw new Exception ('Área no definida!');
		} else if ($preCreatedTask->checkCodeAreaActivity ($codeArea)) {
			throw new Exception ('Código de área ya ha sido creada previamente!');
		}
		if (empty ($areaName)) {
			throw new Exception ('Nombre del area no definida');
		}
		
		if (empty ($status)) {
			throw new Exception ('Seleccionar el estatus de la tarea');
		}
		
		$preCreatedTask->saveAreaActivity (
			AreaActivity::getInstance ()
				->setId ($record)
				->setCodeArea ($codeArea)
				->setAreaName ($areaName)
				->setStatus ($status)
		);
		
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => (!empty($record)) ? 'Se ha actualizado el área' : 'Se ha creado nueva área!',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	header ("Location: index.php?module=preloaded_tasks&action=ListView&tab={$tab}");
