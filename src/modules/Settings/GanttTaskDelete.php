<?php
	require_once ('include/utils/GanttTaskUtils.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	
	global $adb, $current_user, $site_URL;
	setBugSnag ($site_URL);

	try {
		if (!is_admin ($current_user)) {
			throw new Exception ('Acceso denegado');
		}

		$viewId = PlatzillaUtils::purify ($_POST, 'record');
		if (empty ($viewId)) {
			throw new Exception ('No has suministrado el ID de la vista a eliminar');
		}
		GanttTaskUtils::deleteGanttTask ($adb, $viewId);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'El gantt de tareas ha sido eliminado',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	
	header ("Location: index.php?module=Settings&action=KanbanViewListView&parenttab=Settings&tab=gantt-task");
