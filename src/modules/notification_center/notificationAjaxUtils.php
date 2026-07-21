<?php
	require_once ('Smarty_setup.php');
	require_once ('modules/notification_center/lib/NotificationHelper.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb;

	$option  = PlatzillaUtils::purify ($_REQUEST, 'option');
	if($option == 'get_fields') {
		$moduleName   = PlatzillaUtils::purify ($_REQUEST, 'module_name');
		echo NotificationHelper::getFieldsByModule($adb, $moduleName);
		exit();
	} else if($option == 'get_records') {
		$idEmail     = PlatzillaUtils::purify ($_REQUEST, 'idEmail');
		$moduleName  = PlatzillaUtils::purify ($_REQUEST, 'archveModule');
		$fieldData   = PlatzillaUtils::purify ($_REQUEST, 'archveField');
		$searchData  = PlatzillaUtils::purify ($_REQUEST, 'searchField');
		$dataRecords = NotificationHelper::getRecordsInModule($adb, $fieldData, $searchData);
		$dataParts   = explode('@',$fieldData);
		$idField     = str_replace('vtiger_', '',$dataParts[0]);
		$idField .= 'id';
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('MAIL_ID',$idEmail);
		$smarty->assign ('MODULE_NAME',$moduleName);
		$smarty->assign ('FIELD',$dataParts[1]);
		$smarty->assign ('FIELD_ID',$idField);
		$smarty->assign ('RECORDS',$dataRecords);
		echo $smarty->display ('modules/notification_center/recordDataTable.tpl');
		exit();
	} else if ($option == 'set_records') {
		$idEmail    = PlatzillaUtils::purify ($_REQUEST, 'idEmail');
		$moduleName = PlatzillaUtils::purify ($_REQUEST, 'moduleName');
		$record     = PlatzillaUtils::purify ($_REQUEST, 'record');
		echo NotificationHelper::setArchiveEmail($adb, $idEmail, $record, $moduleName);
		exit();
	}
	echo json_encode(array('error' => 'not found'));
	exit();
