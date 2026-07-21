<?php
	require_once('data/Tracker.php');
	require_once('include/logging.php');
	require_once('include/utils/utils.php');
	require_once('modules/Reports/Reports.php');
	require_once('Smarty_setup.php');

	global $app_strings;
	global $app_list_strings;
	global $mod_strings;
	global $current_language;
	$current_module_strings = return_module_language($current_language, 'Reports');

	global $list_max_entries_per_page;
	global $urlPrefix;

	/** @noinspection PhpUndefinedClassInspection */
	$log = LoggerManager::getLogger('report_type');

	global $currentModule;
	global $image_path;
	global $theme;
	$theme_path='themes/'.$theme.'/';
	$image_path=$theme_path.'images/';
	$list_report_form = new vtigerCRM_Smarty;
	$list_report_form->assign('MOD', $mod_strings);
	$list_report_form->assign('APP', $app_strings);
	$list_report_form->assign('IMAGE_PATH',$image_path);
	if (isset($_REQUEST['record']) && $_REQUEST['record']!='') {
	$recordid = vtlib_purify($_REQUEST['record']);
	$oReport = new Reports($recordid);
	$selectedreporttype = $oReport->reporttype;
	} else {
	$selectedreporttype = 'tabular';
	}
	$list_report_form->assign('REPORT_TYPE',$selectedreporttype);
	$list_report_form->display('ReportsType.tpl');
