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
		
		if (empty($drbId)) {
			throw new Exception('Informe de diagnóstico desconocido');
		}
		
		UsersHelper::deleteAgent ($adb, $agentId);
		
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'El Agente ha sido eliminado correctamente'
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	
	header ("Location: index.php?module=panelusuarios&action=AgentsListView&parenttab=Settings");
