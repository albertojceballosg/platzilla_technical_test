<?php
	require_once ('include/utils/utils.php');
	require_once ('include/utils/VtlibUtils.php');

	global $adb;
	if ((!isset ($adb)) || (!$adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$deleteId   = isset ($_REQUEST ['delete_currency_id']) ? $_REQUEST ['delete_currency_id'] : null;
	$transferId = isset ($_REQUEST ['transfer_currency_id']) ? $_REQUEST ['transfer_currency_id'] : null;

	// Transfer all the data refering to currency $del_id to currency $tran_id
	transferCurrency ($deleteId, $transferId);

	// Mark Currency as deleted
	$adb->pquery ('UPDATE vtiger_currency_info SET deleted=1 WHERE id=?', array ($deleteId));

	header ('Location: index.php?action=SettingsAjax&module=Settings&file=CurrencyListView&ajax=true');
