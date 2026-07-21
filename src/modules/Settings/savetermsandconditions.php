<?php
	require_once ('include/utils/CommonUtils.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$termsAndConditions = SettingsUtils::purify ($_REQUEST, 'inventory_tandc');
	$type               = 'Inventory';

	$result = $adb->pquery ('SELECT * FROM vtiger_inventory_tandc WHERE type=?', array ($type));
	if (($result) && ($adb->num_rows ($result) > 0)) {
		$row        = $adb->fetchByAssoc ($result);
		$sql        = 'UPDATE vtiger_inventory_tandc SET tandc=? WHERE id=?';
		$parameters = array ($termsAndConditions, $row ['id']);
	} else {
		$sql        = 'INSERT INTO vtiger_inventory_tandc VALUES (?, ?, ?)';
		$parameters = array ($adb->getUniqueID ('vtiger_inventory_tandc'), $type, $termsAndConditions);
	}
	$adb->pquery ($sql, $parameters);
	header ('Location: index.php?module=Settings&action=OrganizationTermsandConditions&parenttab=Settings');
