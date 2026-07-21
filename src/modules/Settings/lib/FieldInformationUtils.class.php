<?php
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	abstract class FieldInformationUtils {

		private static function getDateTimeFieldDatabaseType ($fieldType) {
			if ($fieldType == 'Date') {
				return 'D';
			} else if ($fieldType == 'Time') {
				return 'TIME';
			} else {
				return '';
			}
		}

		private static function getDateTimeFieldTypeOfData ($fieldType) {
			if ($fieldType == 'Date') {
				return 'D~O';
			} else if ($fieldType == 'Time') {
				return 'T~O';
			} else {
				return '';
			}
		}

		private static function getDateTimeFieldUiType ($fieldType) {
			if ($fieldType == 'Date') {
				return 5;
			} else if ($fieldType == 'Time') {
				return 14;
			} else {
				return '';
			}
		}

		private static function getNumericFieldDatabaseType ($fieldType, $fieldLength, $fieldDecimalLength) {
			if (in_array ($fieldType, array ('Number', 'Currency'))) {
				$databaseFieldLength = ($fieldLength + $fieldDecimalLength + 1);
				return "N({$databaseFieldLength}.{$fieldDecimalLength})";
			} else if ($fieldType == 'Percent') {
				return 'N(5.2)';
			} else {
				return '';
			}
		}

		private static function getNumericFieldTypeOfData ($fieldType, $fieldLength, $fieldDecimalLength) {
			if ($fieldType == 'Number') {
				return "NN~O~{$fieldLength},{$fieldDecimalLength}";
			} else if ($fieldType == 'Percent') {
				return 'N~O~2~2';
			} else if ($fieldType == 'Currency') {
				return "N~O~{$fieldLength},{$fieldDecimalLength}";
			} else {
				return '';
			}
		}

		private static function getNumericFieldUiType ($fieldType) {
			if ($fieldType == 'Number') {
				return 7;
			} else if ($fieldType == 'Percent') {
				return 9;
			} else if ($fieldType == 'Currency') {
				return 71;
			} else {
				return '';
			}
		}

		private static function getSelectionFieldTypeOfData ($fieldType) {
			return $fieldType == 'Checkbox' ? 'C~O' : 'V~O';
		}

		private static function getSelectionFieldUiType ($fieldType) {
			if ($fieldType == 'Picklist') {
				return 15;
			} else if ($fieldType == 'Checkbox') {
				return 56;
			} else if ($fieldType == 'MultiSelectCombo') {
				return 33;
			} else {
				return '';
			}
		}

		private static function getTextFieldDatabaseType ($fieldType, $fieldLength, $fieldProgressMin, $fieldProgressMax, $fieldProgressIni, $fieldProgressOrd) {
			if ($fieldType == 'Text') {
				return "C($fieldLength) default ()";
			} else if ($fieldType == 'Email') {
				return 'C(50) default () ';
			} else if ($fieldType == 'Phone') {
				return 'C(30) default () ';
			} else if (in_array ($fieldType, array ('Picklist', 'RelatedModule', 'RelatedRecords', 'Skype', 'URL'))) {
				return 'C(255) default () ';
			} else if ($fieldType == 'Checkbox') {
				return 'C(3) default 0';
			} else if (in_array ($fieldType, array ('MultiSelectCombo', 'TextArea'))) {
				return 'X';
			} else if ($fieldType == 'Progress_Bar') {
				$value = json_encode (array ('min' => $fieldProgressMin, 'max' => $fieldProgressMax, 'ini' => $fieldProgressIni, 'ord' => $fieldProgressOrd));
				return "C(127) default ({$value})";
			} else {
				return '';
			}
		}

		private static function getTextFieldTypeOfData ($fieldType, $fieldLength) {
			if ($fieldType == 'Text') {
				return "V~O~LE~{$fieldLength}";
			} else if ($fieldType == 'Email') {
				return 'E~O';
			} else if ($fieldType == 'Progress_Bar') {
				return 'V~O~LE~127';
			} else {
				return 'V~O';
			}
		}

		private static function getTextFieldUiType ($fieldType) {
			if ($fieldType == 'Text') {
				return 1;
			} else if ($fieldType == 'Email') {
				return 13;
			} else if ($fieldType == 'Phone') {
				return 11;
			} else if ($fieldType == 'URL') {
				return 17;
			} else if ($fieldType == 'TextArea') {
				return 21;
			} else if ($fieldType == 'Skype') {
				return 85;
			} else if ($fieldType == 'RelatedModule') {
				return 10;
			} else if ($fieldType == 'RelatedRecords') {
				return 404;
			} else if ($fieldType == 'Progress_Bar') {
				return 108;
			} else {
				return '';
			}
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param $tableName
		 *
		 * @return boolean
		 */
		public static function checkAppTable (PearDatabase $adb, $tableName) {
			if (empty($tableName)) {
				return false;
			}
			$tables = $adb->get_tables ();
			return (in_array ($tableName, $tables));
		}
		
		public static function getFieldDatabaseType ($fieldType, $fieldLength, $fieldDecimalLength, $fieldProgressMin, $fieldProgressMax, $fieldProgressIni, $fieldProgressOrd) {
			if (in_array ($fieldType, array ('Checkbox', 'Email', 'MultiSelectCombo', 'Phone', 'Picklist', 'Progress_Bar', 'RelatedModule', 'RelatedRecords', 'Skype', 'Text', 'TextArea', 'URL'))) {
				return self::getTextFieldDatabaseType ($fieldType, $fieldLength, $fieldProgressMin, $fieldProgressMax, $fieldProgressIni, $fieldProgressOrd);
			} else if (in_array ($fieldType, array ('Currency', 'Number', 'Percent'))) {
				return self::getNumericFieldDatabaseType ($fieldType, $fieldLength, $fieldDecimalLength);
			} else if (in_array ($fieldType, array ('Date', 'Time'))) {
				return self::getDateTimeFieldDatabaseType ($fieldType);
			} else {
				return '';
			}
		}

		public static function getFieldDecimalLength ($fieldDecimalLength) {
			return (isset ($fieldDecimalLength)) && (!empty ($fieldDecimalLength)) ? $fieldDecimalLength : 2;
		}

		public static function getFieldDefaultValue ($fieldType, $fieldProgressMin, $fieldProgressMax, $fieldProgressIni, $fieldProgressOrd) {
			return $fieldType == 'Progress_Bar' ? json_encode (array ('min' => $fieldProgressMin, 'max' => $fieldProgressMax, 'ini' => $fieldProgressIni, 'ord' => $fieldProgressOrd)) : '';
		}

		public static function getFieldLength ($fieldLength, $fieldType) {
			if ((isset ($fieldLength)) && (!empty ($fieldLength))) {
				return $fieldLength;
			} else if (in_array ($fieldType, array ('Number', 'Currency'))) {
				return 12;
			} else {
				return 255;
			}
		}

		public static function getFieldQuickCreate ($moduleName) {
			return in_array ($moduleName, array ('Invoice', 'PurchaseOrder', 'Quotes', 'SalesOrder')) ? 3 : 2;
		}

		public static function getFieldSequence (PearDatabase $adb, $blockId) {
			$result = $adb->pquery ('SELECT max(sequence) AS maxsequence FROM vtiger_field WHERE block=?', array ($blockId));
			if (($result) && ($adb->num_rows ($result) > 0)) {
				$row = $adb->fetchByAssoc ($result);
				return $row ['maxsequence'];
			} else {
				return 0;
			}
		}

		public static function getFieldTypeOfData ($fieldType, $fieldLength, $fieldDecimalLength) {
			if (in_array ($fieldType, array ('Email', 'Phone', 'Progress_Bar', 'RelatedModule', 'RelatedRecords', 'Skype', 'Text', 'TextArea', 'URL'))) {
				return self::getTextFieldTypeOfData ($fieldType, $fieldLength);
			} else if (in_array ($fieldType, array ('Currency', 'Number', 'Percent'))) {
				return self::getNumericFieldTypeOfData ($fieldType, $fieldLength, $fieldDecimalLength);
			} else if (in_array ($fieldType, array ('Date', 'Time'))) {
				return self::getDateTimeFieldTypeOfData ($fieldType);
			} else if (in_array ($fieldType, array ('Checkbox', 'MultiSelectCombo', 'Picklist'))) {
				return self::getSelectionFieldTypeOfData ($fieldType);
			} else {
				return '';
			}
		}

		public static function getFieldUiType ($fieldType) {
			if (in_array ($fieldType, array ('Email', 'Phone', 'Progress_Bar', 'RelatedModule', 'RelatedRecords', 'Skype', 'Text', 'TextArea', 'URL'))) {
				return self::getTextFieldUiType ($fieldType);
			} else if (in_array ($fieldType, array ('Currency', 'Number', 'Percent'))) {
				return self::getNumericFieldUiType ($fieldType);
			} else if (in_array ($fieldType, array ('Date', 'Time'))) {
				return self::getDateTimeFieldUiType ($fieldType);
			} else if (in_array ($fieldType, array ('Checkbox', 'MultiSelectCombo', 'Picklist'))) {
				return self::getSelectionFieldUiType ($fieldType);
			} else if (in_array ($fieldType, array ('Attachments'))) {
				return FieldInterface::UI_TYPE_ATTACHMENTS;
			} else {
				return '';
			}
		}

		public static function getTableName ($moduleName, $fieldName) {
			if (empty ($moduleName)) {
				return null;
			}
			$entity     = CRMEntity::getInstance ($moduleName);
			$moduleName = strtolower ($moduleName);
			if (!empty ($fieldName)) {
				return isset ($entity->table_name) ? $entity->table_name : "vtiger_{$moduleName}cf";
			} else {
				return isset ($entity->customFieldTable) ? $entity->customFieldTable [0] : "vtiger_{$moduleName}cf";
			}
		}

		public static function normalizeDefaultValue ($defaultValue, $uiType) {
			if (empty ($defaultValue)) {
				return $defaultValue;
			}
			if ($uiType == 56) {
				if (($defaultValue == 'on') || ($defaultValue == '1')) {
					return '1';
				} else if (($defaultValue == 'off') || ($defaultValue == '0')) {
					return '0';
				} else {
					return '';
				}
			}
			if (in_array ($uiType, array (5, 6, 23))) {
				return getValidDBInsertDateValue ($defaultValue);
			}
			return $defaultValue;
		}

		public static function normalizeMassEditable ($oldMassEditable, $requestedMassEditable) {
			if ($oldMassEditable == 3) {
				return 1;
			}
			return ($requestedMassEditable == 'true') || ($requestedMassEditable == '') ? 1 : 2;
		}

		public static function normalizePresence ($oldPresence, $requestedPresence) {
			if ($oldPresence == 3) {
				return 1;
			}
			return ($requestedPresence == 'true') || ($requestedPresence == '') ? 2 : 1;
		}

		public static function normalizeQuickCreate ($oldQuickCreate, $requestedQuickCreate) {
			if ($oldQuickCreate == 3) {
				return null;
			}
			return ($requestedQuickCreate == 'true') || ($requestedQuickCreate == '') ? 2 : 1;
		}

		public static function normalizeQuickCreateSequence (PearDatabase $adb, $oldQuickCreate, $requestedQuickCreate, $tabId) {
			if (($oldQuickCreate == 3) || (($requestedQuickCreate != 'true') && ($requestedQuickCreate != ''))) {
				return 0;
			}
			$result = $adb->pquery ('SELECT max(quickcreatesequence) AS maxseq FROM vtiger_field WHERE tabid=?', array ($tabId));
			if ((!$result) || (!$adb->num_rows ($result))) {
				return 0;
			}
			$row = $adb->fetchByAssoc ($result);
			return $row ['maxseq'];
		}

		public static function normalizeTypeOfData (CRMEntity $entity, $fieldName, $typeOfData, $mandatory) {
			$dummy = explode ('~', $typeOfData);
			if ((isset ($entity->mandatory_fields)) && (!empty ($entity->mandatory_fields)) && (in_array ($fieldName, $entity->mandatory_fields))) {
				$dummy [1] = 'M';
			} else if (($mandatory == 'true') || ($mandatory == '')) {
				$dummy [1] = 'M';
			} else {
				$dummy [1] = 'O';
			}
			return implode ('~', $dummy);
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param string$fieldName
		 *
		 * @return boolean
		 */
		public static function validateFieldName ($adb, $moduleName, $fieldName) {
			if (empty($fieldName) || empty($moduleName)) {
				return false;
			}

			$result = $adb->pquery (
				'SELECT 
						f.fieldid 
					  FROM 
					  	vtiger_field f
					  INNER JOIN vtiger_tab t ON t.tabid = f.tabid
					  WHERE 
					  	t.name=? AND 
					  	f.fieldname=?',
				array($moduleName, $fieldName)
			);
			$isFound = ($adb->num_rows ($result) > 0);
			DatabaseUtils::closeResult ($result);
			$result = null;
			return ($isFound) ? true : false;
		}

	}
