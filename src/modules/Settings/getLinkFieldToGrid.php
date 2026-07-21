<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');
	global $adb;
	$fieldByName = SettingsUtils::purify ($_REQUEST, 'fieldId');
	$fieldName   = SettingsUtils::purify ($_REQUEST, 'fieldName');
	$idElement   = SettingsUtils::purify ($_REQUEST, 'id');
	$listData    = getSelectField ($adb, $fieldByName);

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('SELECT_FIELD', $listData);
	$smarty->assign ('SELECT_FIELD_NAME', trim ($fieldName));
	$smarty->assign ('SELECT_FIELD_ID', trim ($idElement));
	$selectField = $smarty->fetch ('Settings/selectFieldToGrid.tpl');
	echo $selectField;
	exit ();
