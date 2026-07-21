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
		
		if (empty($drbId)) {
			throw new Exception('Informe de diagnóstico desconocido');
		}
		
		DiagnosticReportBuilderHelper::getInstance ($adb, $platform)->deleteDiagnosticReport ($drbId);
		
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'El informe ha sido eliminado',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	
	header ("Location: index.php?module=diagnostic_report_builder&action=index&parenttab=Settings");

