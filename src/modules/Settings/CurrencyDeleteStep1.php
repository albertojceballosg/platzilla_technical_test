<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb, $app_strings, $mod_strings, $theme;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$currencyId = SettingsUtils::purify ($_REQUEST, 'id');

	$result = $adb->pquery ('SELECT * FROM vtiger_currency_info WHERE id=?', array ($currencyId));
	if (($result) && ($adb->num_rows ($result) > 0)) {
		$row          = $adb->fetchByAssoc ($result);
		$currencyName = getTranslatedCurrencyString ($row ['currency_name']);
	} else {
		$currencyName = '';
	}

	$currencyOptions = array ();
	$result          = $adb->pquery ("SELECT * FROM vtiger_currency_info WHERE currency_status='Active' AND deleted=0 AND id<>?", array ($currencyId));
	if (($result) && ($adb->num_rows ($result) > 0)) {
		while ($row = $adb->fetchByAssoc ($result)) {
			$currencyOptions [] = array (
				'text'  => getTranslatedCurrencyString ($row ['currency_name']),
				'value' => $row ['id'],
			);
		}
	}

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('CURRENCY_ID', $currencyId);
	$smarty->assign ('CURRENCY_NAME', $currencyName);
	$smarty->assign ('CURRENCY_OPTIONS', $currencyOptions);
	$smarty->assign ('IMAGE_CLOSE_URL', vtiger_imageurl ('close.gif', $theme));
	$smarty->assign ('MOD', $mod_strings);
	$smarty->display ('Settings/CurrencyDeleteStep1.tpl');
