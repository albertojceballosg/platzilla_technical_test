<?php

	/**
	 * Function to get the field information from module name and field label

	 * @param $module
	 * @param $label

	 * @return null
	 */
	function getFieldByReportLabel($module, $label) {
	// this is required so the internal cache is populated or reused.
	getColumnFields($module);
	//lookup all the accessible fields
	$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module($module);
	if(empty($cachedModuleFields)) {
		return null;
	}
	foreach ($cachedModuleFields as $fieldInfo) {
		$fieldLabel = str_replace(' ', '_', $fieldInfo['fieldlabel']);
		if($label == $fieldLabel) {
			return $fieldInfo;
		}
	}
	return null;
	}

	function isReferenceUiType($uitype) {
	static $options = array(
	'101',
	'116',
	'117',
	'26',
	'357',
	'50',
	'51',
	'52',
	'53',
	'57',
	'58',
	'59',
	'66',
	'68',
	'73',
	'75',
	'76',
	'77',
	'78',
	'80',
	'81',
);

if(in_array($uitype, $options)) {
	return true;
}
return false;
	}

	/**
	 *
	 * @param ReportRun $report
	 * @param array $picklistArray
	 * @param ADOFieldObject $dbField
	 * @param array $valueArray
	 * @param string $fieldName

	 * @return string
	 */
	function getReportFieldValue($report, $picklistArray, $dbField, $valueArray, $fieldName) {
	$field = '';
	$db = PearDatabase::getInstance();
	$value = $valueArray[$fieldName];
	$fld_type = $dbField->type;
	if (is_numeric ($value) && $fld_type == 'real') {
		 return $value;
	}
	
	list($module, $fieldLabel) = explode('_', $dbField->name, 2);
	$fieldInfo = getFieldByReportLabel($module, $fieldLabel);
	$fieldType = null;
	if(!empty($fieldInfo)) {
		$field = WebserviceField::fromArray($db, $fieldInfo);
		$fieldType = $field->getFieldDataType();
	}
	if(!empty($field)) {
		$flagMainOne = $field->getUIType();
		$flagMainTwo = $field->getFieldName();
	}
	/** @noinspection PhpUndefinedVariableInspection */
	$parameter = array($flagMainOne, $flagMainTwo, $value);
	$fieldvalue = auxGetReportFieldValueMain($parameter, $report, $picklistArray, $fieldType, $valueArray, $dbField, $module);
	if($fieldvalue == '') {
		return '-';
	}
	$fieldvalue = str_replace('<', '&lt;', $fieldvalue);
	$fieldvalue = str_replace('>', '&gt;', $fieldvalue);
	$fieldvalue = decode_html($fieldvalue);

	if (stristr($fieldvalue, '|##|') && empty($fieldType)) {
		$fieldvalue = str_ireplace(' |##| ', ', ', $fieldvalue);
	} else if ($fld_type == 'date' && empty($fieldType)) {
		/** @noinspection PhpParamsInspection */
		$fieldvalue = DateTimeField::convertToUserFormat($fieldvalue);
	} else if ($fld_type == 'datetime' && empty($fieldType)) {
		/** @noinspection PhpParamsInspection */
		$date = new DateTimeField($fieldvalue);
		$fieldvalue = $date->getDisplayDateTimeValue();
	}

	return $fieldvalue;
	}

	function auxGetReportFieldValuePartOne($auxFlag, $auxFieldValue, $auxValue) {
	if($auxFlag == '72') {
		$curid_value = explode('::', $auxValue);
		$currency_id = $curid_value[0];
		$currency_value = $curid_value[1];
		$cur_sym_rate = getCurrencySymbolandCRate($currency_id);
		if($auxValue!='') {
			$formattedCurrencyValue = CurrencyField::convertToUserFormat($currency_value, null, true);
			/** @noinspection PhpParamsInspection */
			$auxFieldValue = CurrencyField::appendCurrencySymbol($formattedCurrencyValue, $cur_sym_rate['symbol']);
		}
	} else if ($_REQUEST['action'] != 'CreateXL') {
		$currencyField = new CurrencyField($auxValue);
		$auxFieldValue = $currencyField->getDisplayValue();
	} else {
		$auxFieldValue = round($auxValue,2);
	}
	return $auxFieldValue;
	}

	function auxGetReportFieldValuePartTwo($auxFlag, $auxModule, $auxValueArray, $auxValue) {
	if($auxModule == 'Calendar' && $auxFlag == 'due_date') {
		$endTime = $auxValueArray['calendar_end_time'];
		if(empty($endTime)) {
			$recordId = $auxValueArray['calendar_id'];
			$endTime = getSingleFieldValue('vtiger_activity', 'time_end', 'activityid', $recordId);
		}
		/** @noinspection PhpParamsInspection */
		$date = new DateTimeField($auxValue.' '.$endTime);
		$auxFieldValue = $date->getDisplayDate();
	} else {
		$auxFieldValue = DateTimeField::convertToUserFormat($auxValue);
	}
	return $auxFieldValue;
	}

	function auxGetReportFieldValuePartThree($auxPicklistArray, $auxFlag, $auxDbField, $auxAppStrings, $auxValue, $auxModule) {
	if(is_array($auxPicklistArray)) {
		if(is_array($auxPicklistArray[$auxDbField->name]) && $auxFlag != 'activitytype' && !in_array($auxValue, $auxPicklistArray[$auxDbField->name])) {
			$auxFieldValue = $auxAppStrings['LBL_NOT_ACCESSIBLE'];
		} else {
			$auxFieldValue = getTranslatedString($auxValue, $auxModule);
		}
	} else {
		$auxFieldValue = getTranslatedString($auxValue, $auxModule);
	}
	return $auxFieldValue;
	}

	function auxGetReportFieldValuePartFour ($auxFieldValue, $auxPicklistArray, $auxValue, $auxDbField, $auxAppsStrings, $auxModule) {
	$translatedValueList = array();
	if(is_array($auxPicklistArray[1])) {
		$valueList = explode(' |##| ', $auxValue);
		foreach ($valueList as $auxValue) {
			if(is_array($auxPicklistArray[1][$auxDbField->name]) && !in_array($auxValue, $auxPicklistArray[1][$auxDbField->name])) {
				$translatedValueList[] = $auxAppsStrings['LBL_NOT_ACCESSIBLE'];
			} else {
				$translatedValueList[] = getTranslatedString($auxValue, $auxModule);
			}
		}
	}
	if (!is_array($auxPicklistArray[1]) || !is_array($auxPicklistArray[1][$auxDbField->name])) {
		$auxFieldValue = str_replace(' |##| ', ', ', $auxValue);
	} else {
		implode(', ', $translatedValueList);
	}
	return $auxFieldValue;
	}

	function auxGetReportFieldValueMain($auxParameter, $AuxReport, $auxPicklistArray, $auxFieldType, $auxValueArray, $auxDbField, $auxModule) {
	global $app_strings;
	$auxFlagMainOne = $auxParameter[0];
	$auxFlagMainTwo = $auxParameter[1];
	$auxValue = $auxParameter[2];
	$auxFieldValue = $auxValue;
	if ($auxFieldType == 'currency' && $auxValue != '') {
		// Some of the currency fields like Unit Price, Total, Sub-total etc of Inventory modules, do not need currency conversion
		$auxFieldValue = auxGetReportFieldValuePartOne($auxFlagMainOne, $auxFieldValue, $auxValue);
	} else if (in_array($auxDbField->name,$AuxReport->ui101_fields) && !empty($auxValue)) {
		$entityNames = getEntityName('Users', $auxValue);
		$auxFieldValue = $entityNames[$auxValue];
	} else if($auxFieldType == 'date' && !empty($auxValue)) {
		$auxFieldValue = auxGetReportFieldValuePartTwo($auxFlagMainTwo, $auxModule, $auxValueArray, $auxValue);
	} else if($auxFieldType == 'datetime' && !empty($auxValue)) {
		$date = new DateTimeField($auxValue);
		$auxFieldValue = $date->getDisplayDateTimeValue();
	} else if($auxFieldType == 'time' && !empty($auxValue) && $auxFlagMainTwo != 'duration_hours') {
		$date = new DateTimeField($auxValue);
		$auxFieldValue = $date->getDisplayTime();
	} else if($auxFieldType == 'picklist' && !empty($auxValue)) {
		$auxFieldValue = auxGetReportFieldValuePartThree($auxPicklistArray, $auxFlagMainTwo, $auxDbField, $app_strings, $auxValue, $auxModule);
	} else if($auxFieldType == 'multipicklist' && !empty($auxValue)) {
		$auxFieldValue = auxGetReportFieldValuePartFour($auxFieldValue, $auxPicklistArray, $auxValue, $auxDbField, $app_strings, $auxModule);
	}
	return $auxFieldValue;
	}
