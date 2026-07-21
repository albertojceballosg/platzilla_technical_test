<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('modules/report_rails/lib/SummaryReportHelper.class.php');

	global $adb, $currentModule, $current_user, $mod_strings, $site_URL;

	setBugSnag ($site_URL);
	
	try {
		$instanceCode = PlatzillaUtils::purify ($_REQUEST, 'instance', null);
		$page         = PlatzillaUtils::purify ($_REQUEST, 'page', 1);
		$report       = PlatzillaUtils::purify ($_REQUEST, 'master_report');
		$returnAction = PlatzillaUtils::purify ($_REQUEST, 'return_action', 'ListView');
		$returnModule = PlatzillaUtils::purify ($_REQUEST, 'return_module', $currentModule);
		$selectedTab  = PlatzillaUtils::purify ($_REQUEST, 'tab', 'SUMMARY_REPORT');
		
		if (empty ($report)) {
			throw new Exception ('¡Reporte semanal no encontrado!');
		}
		$reportMaster = SummaryReportHelper::getMasterReport ($report);
		if (empty ($reportMaster)) {
			throw new Exception ('¡Reporte semanal no encontrado!');
		}
		$instanceCode  = (empty($instanceCode)) ? $reportMaster->getCodeInstance () : $instanceCode;
		if (!empty ($instanceCode)) {
			$adbInstance = AdbManager::getInstance ()->getTargetInstanceAdb ($instanceCode);
		} else {
			$adbInstance = $adb;
		}
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('ADB', $adbInstance);
		$smarty->assign ('AVAILABLE_AGREEMENTS', SummaryReportHelper::fetchAgreements ($report));
		$smarty->assign ('AVAILABLE_PERFORMANCES', SummaryReportHelper::fetchPerformance ($report));
		$smarty->assign ('IS_ADMIN', is_admin ($current_user));
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('AGREEMENTS_STATUS', SummaryReportInterface::AGREEMENTS_STATUS);
		$smarty->assign ('PERFORMANCES_STATUS', SummaryReportInterface::PERFORMANCES_STATUS);
		$smarty->assign ('MASTER_REPORT', $reportMaster);
		$smarty->assign ('RETURN_ACTION', $returnAction);
		$smarty->assign ('SELECTED_TAB', $selectedTab);
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
		$smarty->display ('modules/report_rails/ListViewSummaryReport.tpl');
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
		require_once ('modules/report_rails/ListView.php');
	}
