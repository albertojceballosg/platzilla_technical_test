<?php
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/panelusuarios/lib/UsersHelper.class.php');
	
	global $adb, $current_user, $platform;
	
	try {
		if (!is_admin ($current_user)) {
			throw new Exception ('Acceso denegado');
		}
		
		$agentId  = PlatzillaUtils::purify ($_POST, 'record');
		$status = PlatzillaUtils::purify ($_POST, 'agent_status');
		
		if (empty($agentId)) {
			throw new Exception('Agente desconocido');
		} else if (empty($status)) {
			throw new Exception('Imposible cambiar al Agente a un estado desconocido');
		}
		
		$status = ($status == 'ACTIVE') ? 'INACTIVE' : 'ACTIVE';
		
		UsersHelper::changeStatusToAgent ($adb, $agentId, $status);
		
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'El Agente ha sido actualizado!',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	
	header ("Location: index.php?module=panelusuarios&action=AgentsListView&parenttab=Settings");
