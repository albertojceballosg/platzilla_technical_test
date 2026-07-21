<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/report_rails/lib/SummaryReportHelper.class.php');
	
	global $adb, $app_strings, $mod_strings;
	
	$reportMasterId = PlatzillaUtils::purify ($_REQUEST, 'record');
	$reportContent  = PlatzillaUtils::purify ($_REQUEST, 'report_content');
	try {
		if (empty ($reportMasterId)) {
			throw new Exception ('¡Informe semanal no identificado!');
		}
		if (empty($reportContent) || $reportContent == '<br />') {
			throw new Exception ('¡Oops! No se encontró el contenido del informe.');
		}
		
		SummaryReportHelper::updateMasterReportStatus ($reportMasterId, $reportContent);
		
		header('Access-Control-Allow-Origin: *');
            header('HTTP/1.1 200 OK');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('error' => 'OK'));
        } catch (Exception $e) {
            header('Access-Control-Allow-Origin: *');
            header('HTTP/1.1 200 OK');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('error' => $e->getMessage()));
	}
