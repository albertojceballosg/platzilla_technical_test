<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/report_rails/lib/SummaryReportHelper.class.php');
	
	global $adb, $app_strings, $mod_strings;
	
	$color       = PlatzillaUtils::purify ($_POST, 'performance_color');
	$content     = PlatzillaUtils::purify ($_POST, 'performace_content');
	$iconPath    = PlatzillaUtils::purify ($_POST, 'performance_iconpath');
	$indexColor  = PlatzillaUtils::purify ($_POST, 'index_color');
	$performance = PlatzillaUtils::purify ($_POST, 'performance_index');
	$record      = PlatzillaUtils::purify ($_POST, 'record', null);
	$report      = PlatzillaUtils::purify ($_POST, 'master_report');
	$status      = PlatzillaUtils::purify ($_POST, 'performance_status');
	
	try {
		SummaryReportHelper::savePerformance (
			RailesPerformance::getInstance ()
				->setDescription ($content)
				->setIconPath ($iconPath)
				->setIndexColor ($color)
				->setPerformanceId ($record)
				->setPerformanceStatus ($status)
				->setPerformanceName ($performance)
				->setReportId ($report)
		);
		
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'El index de rendimiento se ha guardado correctamente.',
		);
		header ("Location: index.php?module=report_rails&action=SummaryReportListView&parenttab=Settings&master_report={$report}&tab=PERFORMANCE");
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
				'iserror' => true,
				'message' => $e->getMessage (),
		);
		header ("Location: index.php?module=report_rails&action=SummaryReportListView&parenttab=Settings&master_report={$report}&tab=PERFORMANCE");
	}
	exit ();
	