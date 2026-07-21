<?php
	require_once ('Smarty_setup.php');
	require_once ('modules/diagnostic_report_builder/Objects/ReportBuilderCurrentStatus.php');
	
	$tabDetail = 'modules/diagnostic_report/DetailView.tpl';
	$smarty = new vtigerCRM_Smarty();
	$smarty->assign ('IMAGE_CURRENT_STATUS', ReportBuilderCurrentStatus::IMAGE_CURRENT_STATUS);
	require_once ('modules/Vtiger/DetailView.php');
