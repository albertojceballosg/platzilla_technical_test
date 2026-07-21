<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Managers/PlatformManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/DataViewUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/panelusuarios/lib/UsersHelper.class.php');

	global $adb, $current_user;

	$agentDescription = PlatzillaUtils::purify ($_POST, 'agent_description');
	$agentName        = PlatzillaUtils::purify ($_POST, 'agent_name');
	$agentStatus      = PlatzillaUtils::purify ($_POST, 'agent_status');
	$instances        = PlatzillaUtils::purify ($_POST, 'instances');
	$recordId         = PlatzillaUtils::purify ($_POST, 'record', null);
	$userId           = PlatzillaUtils::purify ($_POST, 'user');
	$isInstance       = !empty ($_SESSION ['platInstancia']);

	try {
		if (empty ($userId)) {
			throw new Exception ('Agente no identificado');
		} else if (empty ($instances)) {
			throw new Exception ('No se han asociado instancias al agente');
		}
		
		if (!empty ($recordId) && $recordId != $userId) {
			UsersHelper::deleteAgent ($adb, $recordId);
			$recordId = null;
		}
		
		UsersHelper::saveAgent (
			$adb,
			$recordId,
			Agents::getInstance ()
				->setId ($userId)
				->setName ($agentName)
				->setDescription ($agentDescription)
				->setStatus ($agentStatus),
			$instances
		);
		
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'El Agente ha sido guardado',
		);
		header ('Location: index.php?module=panelusuarios&action=AgentsListView&parenttab=Settings');
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
			'data'    => isset ($course) ? $course->serialize () : null,
		);
		$recordUriPart = !empty ($courseId) ? "&record={$courseId}" : '';
		header ("Location: index.php?module=panelusuarios&action=AgentsEditView&record={$userId}");
	}
	exit ();
