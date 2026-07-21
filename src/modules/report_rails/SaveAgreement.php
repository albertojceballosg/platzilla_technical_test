<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/report_rails/lib/SummaryReportHelper.class.php');
	
	global $adb, $app_strings, $mod_strings;
	
	$agreementContent = PlatzillaUtils::purify ($_POST, 'agreement_content');
	$agreementModule  = PlatzillaUtils::purify ($_POST, 'agreement_module');
	$agreementName    = PlatzillaUtils::purify ($_POST,'agreement_name');
	$agreementTitle   = PlatzillaUtils::purify ($_POST, 'agreement_title');
	$agreementUsers   = PlatzillaUtils::purify ($_POST, 'agreement_users');
	$entityId         = PlatzillaUtils::purify ($_POST, 'entity');
	$entityType       = PlatzillaUtils::purify ($_POST, 'entity_type');
	$record           = PlatzillaUtils::purify ($_POST, 'record', null);
	$report           = PlatzillaUtils::purify ($_POST, 'master_report');
	$sequence         = PlatzillaUtils::purify ($_POST, 'agreement_sequence');
	$status           = PlatzillaUtils::purify ($_POST, 'agreement_status');
	$updateInstance   = PlatzillaUtils::purify ($_POST, 'update_istance', 'no');
	
	$stringName    = (!empty($agreementName)) ? $agreementName : md5 ($agreementTitle . '_' . $record);
	
	try {
		$agreementObj = RailesAgreements::getInstance()
			->setAgreement ($agreementTitle)
			->setAgreementId  ($record)
			->setAgreementName ($stringName)
			->setAgreementStatus ($status)
			->setDescription ($agreementContent)
			->setExecution ($entityId)
			->setReportId ($report)
			->setSequence ($sequence)
			->setTabName ($agreementModule)
			->setUsersInvolved ($agreementUsers);
		SummaryReportHelper::saveAgreement ($agreementObj);
		
		if (!empty($agreementName) && $updateInstance == 'yes') {
			SummaryReportHelper::updateAgreement ($agreementObj);
		}
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'El acuerdo se ha guardado correctamente.',
		);
		header ("Location: index.php?module=report_rails&action=SummaryReportListView&parenttab=Settings&master_report={$report}&tab=AGREEMENTS");
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
				'iserror' => true,
				'message' => $e->getMessage (),
		);
		header ("Location: index.php?module=report_rails&action=SummaryReportListView&parenttab=Settings&master_report={$report}&tab=AGREEMENTS");
	}
	exit ();
