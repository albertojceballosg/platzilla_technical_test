<?php
	require_once ('Smarty_setup.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb, $app_strings, $current_language, $plat;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$termsMode = SettingsUtils::purify ($_REQUEST, 'inv_terms_mode');

	$result = $adb->query ('SELECT * FROM vtiger_inventory_tandc');
	if (($result) && ($adb->num_rows ($result) > 0)) {
		$row                = $adb->fetchByAssoc ($result);
		$termsAndConditions = !$termsMode ? nl2br ($row ['tandc']) : $row ['tandc'];
	}

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('INV_TERMS_MODE', !empty ($termsMode) ? $termsMode : 'view');
	$smarty->assign ('MOD', return_module_language ($current_language, 'Settings'));
	if (isset ($termsAndConditions)) {
		$smarty->assign ('INV_TERMSANDCONDITIONS', $termsAndConditions);
	}
	$smarty->display ('Settings/InventoryTerms.tpl');
