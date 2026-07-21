<?php
	require_once ('modules/Settings/lib/FieldValidationsHelper.class.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$fieldId        = SettingsUtils::purify ($_REQUEST, 'fieldid');
	$fieldName      = SettingsUtils::purify ($_REQUEST, 'fieldname');
	$fieldValue     = SettingsUtils::purify ($_REQUEST, 'fieldValue');
	$maxValue       = SettingsUtils::purify ($_REQUEST, 'maxvalue');
	$minValue       = SettingsUtils::purify ($_REQUEST, 'minvalue');
	$moduleName     = SettingsUtils::purify ($_REQUEST, 'modulename');
	$recordId       = SettingsUtils::purify ($_REQUEST, 'recordid');
	$subMode        = SettingsUtils::purify ($_REQUEST, 'sub_mode');
	$tableName      = SettingsUtils::purify ($_REQUEST, 'tablename');
	$uiType         = SettingsUtils::purify ($_REQUEST, 'uitype');
	$validationType = SettingsUtils::purify ($_REQUEST, 'validationtype');

	try {
		if ($subMode == 'validationField') {
			FieldValidationsHelper::validateFields (
				$adb,
				array (
					'fieldname'      => $fieldName,
					'fieldValue'     => $fieldValue,
					'maxvalue'       => $maxValue,
					'minvalue'       => $minValue,
					'modulename'     => $moduleName,
					'recordid'       => $recordId,
					'tablename'      => $tableName,
					'validationtype' => $validationType,
				)
			);
		} else if ($subMode == 'validationCheckFields') {
			echo json_encode (FieldValidationsHelper::getModuleValidationRecords ($adb, $moduleName));
		} else if ($subMode == 'insertValidationField') {
			echo FieldValidationsHelper::updateValidationRecords ($adb, $moduleName, $fieldName, $validationType, $minValue, $maxValue);
		} else if ($subMode == 'valuesExtractedValidation') {
			echo json_encode (FieldValidationsHelper::getFieldValidationRecords ($adb, $moduleName, $fieldName));
		}
	} catch (Exception $e) {
		echo $e->getMessage ();
	}
	exit ();
