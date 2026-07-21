<?php
	require_once ('include/platzilla/Managers/FieldManager.php');
	require_once ('include/platzilla/Managers/KanbanViewManager.php');
	require_once ('include/utils/KanbanTaskUtils.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	
	global $adb, $current_user, $site_URL;
	
	$tabName = PlatzillaUtils::purify ($_POST, 'tabname');
	$viewId  = PlatzillaUtils::purify ($_POST, 'record');
	
	try {
		if (!is_admin ($current_user)) {
			throw new Exception ('No estás autorizado a realizar acciones de configuración');
		} else if (empty($tabName)) {
			throw new Exception ('Uoops! no ha seleccionado el modulo');
		}
		
		$parameters = array (
			'detail_view' => PlatzillaUtils::purify ($_POST, 'detailview'),
			'list_view'   => PlatzillaUtils::purify ($_POST, 'listview'),
			'tab_name'    => $tabName,
			'user_id'     => $current_user->id,
			'view_id'     => $viewId
		);
		
		KanbanTaskUtils::saveKanbanTask ($adb, $parameters);
		
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'El kanban de tareas ha sido guardado',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
			'data'    => !empty ($arguments) ? $arguments : null,
		);
		
	}
	header ("Location: index.php?module=Settings&action=KanbanViewListView&parenttab=Settings&tab=kanban-task");
