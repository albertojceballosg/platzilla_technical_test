<?php
	require_once ('Smarty_setup.php');
	require_once ('include/CustomFieldUtil.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/VtlibUtils.php');
	require_once ('modules/Settings/lib/AddBlockFieldHelper.class.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb, $app_strings, $currentModule, $mod_strings, $theme;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$blockId    = SettingsUtils::purify ($_REQUEST, 'blockid', '');
	$blockName  = SettingsUtils::purify ($_REQUEST, 'blockname', '');
	$fieldId    = SettingsUtils::purify ($_REQUEST, 'fieldselect', '');
	$mode       = SettingsUtils::purify ($_REQUEST, 'mode');
	$moduleName = SettingsUtils::purify ($_REQUEST, 'fld_module');
	$tabId      = SettingsUtils::purify ($_REQUEST, 'tabid', '');
	$uiType     = SettingsUtils::purify ($_REQUEST, 'uitype', 1);
	$fields     = AddBlockFieldHelper::getModuleFields ($adb, $moduleName, $tabId, $blockId);

	if ($mode == 'edit') {
		$types       = array (
			'0'  => 'Text',
			'1'  => 'Number',
			'2'  => 'Percent',
			'3'  => 'Currency',
			'4'  => 'Date',
			'5'  => 'Email',
			'6'  => 'Phone',
			'7'  => 'Picklist',
			'8'  => 'URL',
			'9'  => 'Checkbox',
			'11' => 'MultiSelectCombo',
			'12' => 'Skype',
			'13' => 'Time',
		);
		$columnName  = getCustomFieldData ($tabId, $fieldId, 'columnname');
		$typeOfData  = getCustomFieldData ($tabId, $fieldId, 'typeofdata');
		$typeName    = getCustomFieldTypeName ($uiType != '' ? $uiType : 1);
		$lengthValue = getFldTypeandLengthValue ($typeName, $typeOfData);
		list ($type, $length, $decimalValue) = explode (';', $lengthValue);
		$selectedValue = $types [ $type ];
	} else {
		$columnName    = '';
		$selectedValue = '';
	}

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('APPLICATION_STRINGS', $app_strings);
	$smarty->assign ('BLOCK_ID', $blockId);
	$smarty->assign ('BLOCK_NAME', $blockName);
	$smarty->assign ('COLUMN_NAME', $columnName);
	$smarty->assign ('CURRENT_MODULE', $currentModule);
	$smarty->assign ('FIELD_SELECT', $fieldId);
	$smarty->assign ('FIELDS', $fields);
	$smarty->assign ('LBL_MOVE_BLOCK_FIELD', getTranslatedString ('LBL_MOVE_BLOCK_FIELD'));
	$smarty->assign ('LBL_SELECT_FIELD_TO_MOVE', getTranslatedString ('LBL_SELECT_FIELD_TO_MOVE'));
	$smarty->assign ('MODE', $mode);
	$smarty->assign ('MODULE', $moduleName);
	$smarty->assign ('SELECTED_VALUE', $selectedValue);
	$smarty->assign ('TAB_ID', $tabId);
	$smarty->assign ('URL_IMAGE_CLOSE', vtiger_imageurl ('close.gif', $theme));
	echo $smarty->fetch ('Settings/AddBlockField.tpl');
