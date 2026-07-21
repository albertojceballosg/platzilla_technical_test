<?php
	require_once('data/Tracker.php');
	require_once('Smarty_setup.php');
	require_once('include/logging.php');
	require_once('include/utils/utils.php');
	require_once('modules/Reports/Reports.php');

	global $app_strings;
	global $app_list_strings;
	global $mod_strings;
	global $current_language;
	global $ogReport;
	$current_module_strings = return_module_language($current_language, 'Reports');

	global $list_max_entries_per_page;
	global $urlPrefix;

	/** @noinspection PhpUndefinedClassInspection */
	$log = LoggerManager::getLogger('report_type');

	global $currentModule;
	global $image_path;
	global $theme;
	$report_column_tot=new vtigerCRM_Smarty;
	$report_column_tot->assign('MOD', $mod_strings);
	$report_column_tot->assign('APP', $app_strings);
	$report_column_tot->assign('IMAGE_PATH',$image_path);

	if (isset($_REQUEST['record']) && $_REQUEST['record']!='') {
	$recordid = vtlib_purify($_REQUEST['record']);
	$oReport = new Reports($recordid);
	$oRep = new Reports();
	$secondarymodule = '';
	$secondarymodules = array();

	if(!empty($oRep->related_modules[$oReport->primodule])) {
		foreach($oRep->related_modules[$oReport->primodule] as $key => $value){
			if(isset($_REQUEST['secondarymodule_'.$value])) {
				$secondarymodules []= vtlib_purify($_REQUEST['secondarymodule_'.$value]);
			}
		}
	}
	$secondarymodule = implode(':',$secondarymodules);
	$oReport->secmodule = $secondarymodule;

	$blockOne = $oReport->sgetColumntoTotalSelected($oReport->primodule,$oReport->secmodule,$recordid);
	$report_column_tot->assign('BLOCK1',$blockOne);
	$report_column_tot->assign('RECORDID',$recordid);
	} else {
	$primarymodule = vtlib_purify($_REQUEST['primarymodule']);
	$oReport = new Reports();
	$secondarymodule = array();
	if(!empty($ogReport->related_modules[$primarymodule])) {
		foreach($ogReport->related_modules[$primarymodule] as $key => $value){
			$secondarymodule[] = vtlib_purify($_REQUEST['secondarymodule_'.$value]);
		}
	}
	$blockOne = $oReport->sgetColumntoTotal($primarymodule,$secondarymodule);
	$report_column_tot->assign('BLOCK1',$blockOne);
	}
	// added to avoid displaying "No data available to total" when using related modules in report.
	if (count($blockOne[0]) == 0 && count($blockOne[1])==0) {
	$report_column_tot->assign('ROWS_COUNT', 0);
	} else {
	$report_column_tot->assign('ROWS_COUNT', '-1');
	}
	$report_column_tot->display('ReportColumnsTotal.tpl');
