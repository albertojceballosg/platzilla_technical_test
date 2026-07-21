<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/panelusuarios/lib/UsersHelper.class.php');
	require_once ('modules/report_rails/lib/SummaryReportHelper.class.php');
	
	global $adb, $app_strings, $currentModule, $mod_strings;

	$agreementId  = PlatzillaUtils::purify ($_REQUEST, 'record');
	$reportId     = PlatzillaUtils::purify ($_REQUEST, 'master_report');
	$returnAction = PlatzillaUtils::purify ($_REQUEST, 'return_action', 'ListView');
	
	$smarty = new vtigerCRM_Smarty ();
	if (isset ($_SESSION ['flashmessage'])) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		unset ($_SESSION ['flashmessage']);
	}
	try {
		if (!empty ($agreementId)) {
			$agreement = SummaryReportHelper::getAgreement ($agreementId);
			$userIds   = (!empty($agreement->getUsersInvolved ())) ? array_column ($agreement->getUsersInvolved (), 'userid') : array();
			$agreement->setUsersInvolved ($userIds);
			if (empty ($agreement)) {
				throw new Exception ('Acuerdo no encontrado');
			}
		}
		$hasBeenPublished = false;
		if (!empty ($reportId)) {
			$report = SummaryReportHelper::getMasterReport ($reportId);
			if (empty ($report)) {
				throw new Exception ('Informe no encontrado');
			}
			$hasBeenPublished = SummaryReportHelper::hasBeenPublished ($report);
			$availableModules = SummaryReportHelper::fetchAvailableModules ($report->getCodeInstance ());
			$availableUsers   = SummaryReportHelper::fetchAvailableUsers ($report->getCodeInstance (), $_SESSION ['plaform']);
		}
		
		
		$smarty->assign ('AGREEMENT', $agreement);
		$smarty->assign ('AGREEMENTS_STATUS', SummaryReportInterface::AGREEMENTS_STATUS);
		$smarty->assign ('AVAILABLE_MODULES', (isset ($availableModules) ? $availableModules : null));
		$smarty->assign ('AVAILABLE_USERS', (isset ($availableUsers) ? $availableUsers : null));
		$smarty->assign ('INSTANCE_CODE', $report->getCodeInstance ());
		$smarty->assign ('MASTER_REPORT_ID', $reportId);
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('REPORT_PUBLISHED',$hasBeenPublished);
		$smarty->assign ('RETURN_ACTION', $returnAction);
		$smarty->display ('modules/report_rails/AgreementEditView.tpl');
	} catch (Exception $e) {
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', 'index.php?module=');
		$smarty->display ('Message.tpl');
	}
