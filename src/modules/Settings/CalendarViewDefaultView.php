<?php
	require_once ('include/utils/CalendarViewUtils.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $current_user;

	try {
		if (!is_admin ($current_user)) {
			throw new Exception ('Acceso denegado');
		}

		$viewId = PlatzillaUtils::purify ($_POST, 'record');
		if (empty ($viewId)) {
			throw new Exception ('No has suministrado el ID de la vista marcar por defecto');
		}

		CalendarViewUtils::setDefaultView ($adb, $viewId);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'La vista ha sido actualizada',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	header ('Location: index.php?module=Settings&action=CalendarViewListView&parenttab=Settings');
