<?php
	require_once ('include/utils/KanbanViewUtils.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	/**
	 * Archivo que se encarga de eliminar una vista Kanban
	 * desde el configurador Kanban. se accede via AJAX
	 *
	 * @var PearDatabase $adb
	 * @var string $current_user
	 */
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

		KanbanViewUtils::deleteView ($adb, $viewId);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'La vista ha sido eliminada',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}

	header ('Location: index.php?module=Settings&action=KanbanViewListView&parenttab=Settings');
