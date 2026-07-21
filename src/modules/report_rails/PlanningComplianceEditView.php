<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/panelusuarios/lib/UsersHelper.class.php');
	require_once ('modules/report_rails/lib/SummaryReportHelper.class.php');
	
	global $adb, $app_strings, $currentModule, $mod_strings;

	$planComplianceId = PlatzillaUtils::purify ($_REQUEST, 'record');
	$reportId         = PlatzillaUtils::purify ($_REQUEST, 'master_report');
	$returnAction     = PlatzillaUtils::purify ($_REQUEST, 'return_action', 'ListView');
	
	$smarty = new vtigerCRM_Smarty ();
	if (isset ($_SESSION ['flashmessage'])) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		unset ($_SESSION ['flashmessage']);
	}
	try {
		if (!empty ($planComplianceId)) {
			$planCompliance = SummaryReportHelper::getPerformance ($planComplianceId);
			if (empty ($planCompliance)) {
				throw new Exception ('Index de rendimiento no encontrado');
			}
		}
		$smarty->assign ('AVAILABLE_AGENTS', UsersHelper::FetchAgents ($adb, true));
		$smarty->assign ('MASTER_REPORT_ID', $reportId);
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('PERFORMANCE', $planCompliance);
		$smarty->assign ('PERFORMANCES_ICONPATH', SummaryReportInterface::PERFORMANCES_ICON_PATH);
		$smarty->assign ('PERFORMANCES_STATUS', SummaryReportInterface::PERFORMANCES_STATUS);
		$smarty->assign ('RETURN_ACTION', $returnAction);
		$smarty->display ('modules/report_rails/PerformanceEditView.tpl');
	} catch (Exception $e) {
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', 'index.php?module=');
		$smarty->display ('Message.tpl');
	}
