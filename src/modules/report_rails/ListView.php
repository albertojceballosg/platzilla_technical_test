<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/report_rails/lib/SummaryReportHelper.class.php');
	require_once ('modules/Home/lib/WorkingDayUtils.class.php');

	global $adb, $currentModule, $current_user, $mod_strings, $site_URL;

	setBugSnag ($site_URL);

	$page         = PlatzillaUtils::purify ($_GET, 'page', 1);
	$selectedTab  = PlatzillaUtils::purify ($_GET, 'tab', 'SUMMARY_REPORT');
	$returnAction = PlatzillaUtils::purify ($_GET, 'return_action', 'ListView');
	$returnModule = PlatzillaUtils::purify ($_GET, 'return_module', $currentModule);
	
	$smarty = new vtigerCRM_Smarty ();
	try {
		$firstDay    = WorkingDayUtils::getFirstDayWeek ($adb);
		
		$offsetMonth = 3;
		$formDate    = date ('Y-m-d', strtotime("{$firstDay} - 1 week"));
		$toDate      = date ('Y-m-d', strtotime("{$firstDay} - 0 week"));
		$period      = "{$formDate}@{$toDate}";
		
		$smarty->assign ('AVAILABLE_AGENTS', UsersHelper::FetchAgents ($adb, true));
		$smarty->assign ('FIRST_DAY', $firstDay);
		$smarty->assign ('IS_ADMIN', is_admin ($current_user));
		$smarty->assign ('MASTER_REPORT', SummaryReportHelper::fetchMasterReport ());
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('OFFSET_MONTH', $offsetMonth);
		$smarty->assign ('PERFORMANCES_STATUS', SummaryReportInterface::PERFORMANCES_STATUS);
		$smarty->assign ('RETURN_ACTION', $returnAction);
		$smarty->assign ('SELECTED_TAB', $selectedTab);
		$smarty->assign ('SELECTED_WEEK', $period);
		
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
		$smarty->display ('modules/report_rails/ListView.tpl');
	} catch (Exception $e) {
		$smarty->assign ('AVAILABLE_PERFORMANCES', null);
		$smarty->assign ('IS_ERROR', true);
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->display ('modules/report_rails/ListView.tpl');
	}
