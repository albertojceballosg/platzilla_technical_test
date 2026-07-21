<?php
	require_once('include/utils/utils.php');
	require_once('modules/Reports/ScheduledReports.php');

	global $theme;
	global $current_user;
	global $adb;
	global $current_language;
	global $app_strings;
	global $mod_strings;
	$theme_path='themes/'.$theme.'/';
	$image_path=$theme_path.'images/';
	$smarty = new vtigerCRM_Smarty;
	/** @noinspection PhpUndefinedClassInspection */
	$log = LoggerManager::getLogger('report_type');

	$smarty->assign('MOD', return_module_language($current_language,'Reports'));
	$smarty->assign('IMAGE_PATH',$image_path);
	$smarty->assign('APP', $app_strings);
	$smarty->assign('CMOD', $mod_strings);

	$availableUsersHTML = VTScheduledReport::getAvailableUsersHtml();
	$availableGroupsHTML = VTScheduledReport::getAvailableGroupsHtml();
	$availableRolesHTML = VTScheduledReport::getAvailableRolesHtml();
	$availableRolesAndSubHTML = VTScheduledReport::getAvailableRolesAndSubordinatesHtml();

	$smarty->assign('AVAILABLE_USERS', $availableUsersHTML);
	$smarty->assign('AVAILABLE_GROUPS', $availableGroupsHTML);
	$smarty->assign('AVAILABLE_ROLES', $availableRolesHTML);
	$smarty->assign('AVAILABLE_ROLESANDSUB', $availableRolesAndSubHTML);

	$reportid = vtlib_purify($_REQUEST['record']);

	$scheduledReport = new VTScheduledReport($adb, $current_user, $reportid);
	$scheduledReport->getReportScheduleInfo();

	$smarty->assign('IS_SCHEDULED', $scheduledReport->isScheduled);
	$smarty->assign('REPORT_FORMAT', $scheduledReport->scheduledFormat);

	$selectedRecipientsHTML = $scheduledReport->getSelectedRecipientsHtml();
	$smarty->assign('SELECTED_RECIPIENTS', $selectedRecipientsHTML);

	$smarty->assign('schtypeid',$scheduledReport->scheduledInterval['scheduletype']);
	$smarty->assign('schtime',$scheduledReport->scheduledInterval['time']);
	$smarty->assign('schday',$scheduledReport->scheduledInterval['date']);
	$smarty->assign('schweek',$scheduledReport->scheduledInterval['day']);
	$smarty->assign('schmonth',$scheduledReport->scheduledInterval['month']);

	$smarty->display('ReportsScheduleEmail.tpl');
