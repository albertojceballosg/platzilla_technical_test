<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/ListViewUtils.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb, $app_strings, $currency_name, $mod_strings;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$currencyId = SettingsUtils::purify ($_REQUEST, 'record');
	$detailView = SettingsUtils::purify ($_REQUEST, 'detailview');

	$selectedCurrency = null;
	$result           = $adb->pquery ('SELECT * FROM vtiger_currency_info WHERE id=? AND deleted=0', array ($currencyId));
	$selectedCurrency = ($result) && ($adb->num_rows ($result)) ? $adb->fetchByAssoc ($result) : null;

	if ($currencyId) {
		$result     = $adb->pquery ('SELECT * FROM vtiger_users WHERE currency_id=?', ($currencyId));
		$isAssigned = ($result) && ($adb->num_rows ($result) > 0) ? true : false;
	} else {
		$isAssigned = false;
	}

	$unusedCurrencies = array ();
	if ($currencyId) {
		$result = $adb->pquery (
			'SELECT currency_name, currency_code, currency_symbol FROM vtiger_currencies WHERE currency_code NOT IN (
				SELECT currency_code FROM vtiger_currency_info WHERE deleted=0 AND id<>?
			)',
			array ($currencyId)
		);
	} else {
		$result = $adb->query (
			'SELECT currency_name, currency_code, currency_symbol FROM vtiger_currencies WHERE currency_code NOT IN (
				SELECT currency_code FROM vtiger_currency_info WHERE deleted=0
			)'
		);
	}
	if (($result) && ($adb->num_rows ($result))) {
		while ($row = $adb->fetchByAssoc ($result)) {
			$unusedCurrencies [ decode_html ($row ['currency_name']) ] = array (decode_html ($row ['currency_code']), decode_html ($row ['currency_symbol']));
		}
	}
	ksort ($unusedCurrencies);

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('CURRENCY_CONVERSION_RATE', isset ($selectedCurrency ['conversion_rate']) ? $selectedCurrency ['conversion_rate'] : null);
	$smarty->assign ('CURRENCY_CODE', isset ($selectedCurrency ['currency_code']) ? getTranslatedCurrencyString ($selectedCurrency ['currency_code']) : null);
	$smarty->assign ('CURRENCY_ID', $currencyId);
	$smarty->assign ('CURRENCY_IS_ASSIGNED', $isAssigned);
	$smarty->assign ('CURRENCY_NAME', isset ($selectedCurrency ['currency_name']) ? $selectedCurrency ['currency_name'] : null);
	$smarty->assign ('CURRENCY_STATUS', isset ($selectedCurrency ['currency_status']) ? $selectedCurrency ['currency_status'] : null);
	$smarty->assign ('CURRENCY_SYMBOL', isset ($selectedCurrency ['currency_symbol']) ? decode_html ($selectedCurrency ['currency_symbol']) : null);
	$smarty->assign ('MASTER_CURRENCY', $currency_name);
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('PARENTTAB', getParentTab ());
	$smarty->assign ('UNUSED_CURRENCIES', $unusedCurrencies);
	if (!empty ($detailView)) {
		$smarty->display ('Settings/CurrencyDetailView.tpl');
	} else {
		$smarty->display ('Settings/CurrencyEditView.tpl');
	}
