<?php
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/diagnostic_report_builder/lib/DiagnosticReportBuilderHelper.class.php');
	
	global $adb, $current_user, $platform;
	
	try {
		if (!is_admin ($current_user)) {
			throw new Exception ('Acceso denegado');
		}
		
		$drbId  = PlatzillaUtils::purify ($_POST, 'record');
		$status = PlatzillaUtils::purify ($_POST, 'status');
		
		if (empty($drbId)) {
			throw new Exception('Informe de diagnóstico desconocido');
		} else if (empty($status)) {
			throw new Exception('Imposible cambiar el informe a un estado desconocido');
		}
		
		$status = ($status == 'ENABLED') ? 'DISABLED' : 'ENABLED';
		
		DiagnosticReportBuilderHelper::getInstance ($adb, $platform)->changeStatusToDiagnosticReport ($drbId, $status);
		
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'El informe ha sido actualizado!',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	
	header ("Location: index.php?module=diagnostic_report_builder&action=index&parenttab=Settings");


