<?php
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$id             = SettingsUtils::purify ($_REQUEST, 'record');
	$name           = SettingsUtils::purify ($_REQUEST, 'currency_name');
	$code           = SettingsUtils::purify ($_REQUEST, 'currency_code');
	$symbol         = SettingsUtils::purify ($_REQUEST, 'currency_symbol');
	$status         = SettingsUtils::purify ($_REQUEST, 'currency_status');
	$conversionRate = SettingsUtils::purify ($_REQUEST, 'conversion_rate');
	$transferToId   = SettingsUtils::purify ($_REQUEST, 'transfer_currency_id');
	$parentTabId    = SettingsUtils::purify ($_REQUEST, 'parenttab', '');

	$status = !empty ($status) ? $status : 'Active';

	if ($id) {
		$result = $adb->pquery ('SELECT currency_status FROM vtiger_currency_info WHERE id=?', array ($id));
		if (($result) && ($adb->num_rows ($result) > 0)) {
			$row       = $adb->fetchByAssoc ($result);
			$oldStatus = $row ['currency_status'];
		} else {
			$oldStatus = null;
		}

		if (($status == 'Inactive') && ($status != $oldStatus) && ($transferToId)) {
			transferCurrency ($id, $transferToId);
		}

		$sql        = 'UPDATE vtiger_currency_info SET currency_name=?, currency_code=?, currency_symbol=?, conversion_rate=?, currency_status=? WHERE id=?';
		$parameters = array ($name, $code, $symbol, $conversionRate, $status, $id);
	} else {
		$sql    = "INSERT INTO vtiger_currency_info (id, currency_name, currency_code, currency_symbol, conversion_rate, currency_status, defaultid, deleted) VALUES (?, ?, ?, ?, ?, ?, '0', '0')";
		$parameters = array ($adb->getUniqueID ('vtiger_currency_info'), $name, $code, $symbol, $conversionRate, $status);
	}
	$adb->pquery ($sql, $parameters);
	header ("Location: index.php?module=Settings&action=CurrencyListView&parenttab={$parentTabId}");
