<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb, $app_strings, $mod_strings;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$isAjaxRequest = SettingsUtils::purify ($_REQUEST, 'ajax');

	$currencies = array ();
	$result     = $adb->query ('SELECT * FROM vtiger_currency_info WHERE deleted=0');
	if (($result) && ($adb->num_rows ($result) > 0)) {
		while ($row = $adb->fetchByAssoc ($result)) {
			$currencies [] = array (
				'id'        => $row ['id'],
				'code'      => $row ['currency_code'],
				'crate'     => $row ['conversion_rate'],
				'defaultid' => $row ['defaultid'],
				'name'      => getTranslatedCurrencyString ($row ['currency_name']),
				'status'    => $row ['currency_status'],
				'symbol'    => $row ['currency_symbol'],
			);
		}
	}

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('CURRENCIES', $currencies);
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('PARENTTAB', getParentTab ());
	if (!empty ($isAjaxRequest)) {
		$smarty->display ('Settings/CurrencyListViewEntries.tpl');
	} else {
		$smarty->display ('Settings/CurrencyListView.tpl');
	}
