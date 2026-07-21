<?php
	require_once ('include/CustomFieldUtil.php');

	abstract class CreateCustomFieldHelper {

		public static function getModuleId ($tabId, $fieldId, $fieldModule, $activityType) {
			if ((!$fieldId) && ($fieldModule == 'Calendar') && ($activityType) && (in_array ($activityType, array ('E', 'T')))) {
				$moduleId = $activityType == 'E' ? '16' : '9';
			} else {
				$moduleId = $tabId;
			}
			return $moduleId;
		}

		private static function getFieldData (PearDatabase $adb, $moduleId, $fieldId) {
			$result = $adb->pquery (
				'SELECT columnname, fieldlabel, typeofdata FROM vtiger_field WHERE tabid=? AND fieldid=? AND presence IN (0, 2)',
				array ($moduleId, $fieldId)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array (
					'columnname' => '',
					'label'      => '',
					'typeofdata' => '',
				);
			}

			return $adb->fetchByAssoc ($result);
		}

		private static function getPicklistValue (PearDatabase $adb, $fieldColumnName, $fieldType) {
			if ((!$fieldType) || (!in_array ($fieldType, array (7, 11)))) {
				return '';
			}

			$columnName = $adb->sql_escape_string ($fieldColumnName);
			$result     = $adb->query ("SELECT * FROM vtiger_{$columnName}");
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return '';
			}

			$values = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$values = $row [ $fieldColumnName ];
			}
			return join ("\n", $values);
		}

		public static function getCustomFieldData (PearDatabase $adb, $moduleId, $fieldId, $fieldUiType, $isDuplicate) {
			if ((!isset ($fieldid)) || ($fieldid == '')) {
				return array (
					'columnname'    => '',
					'decimalvalue'  => '',
					'label'         => '',
					'length'        => '',
					'picklistvalue' => '',
					'type'          => '',
					'typename'      => '',
					'typeofdata'    => '',
				);
			}

			$fieldData     = self::getFieldData ($adb, $moduleId, $fieldId);
			$typeName      = getCustomFieldTypeName ($fieldUiType);
			$typeAndLength = getFldTypeandLengthValue ($typeName, $fieldData ['typeofdata']);
			list ($type, $length, $decimalValue) = explode (';', $typeAndLength);

			$fieldData ['typename'] = $typeName;
			$fieldData ['type']     = $type;
			if (!$isDuplicate) {
				$fieldData ['length']        = $length;
				$fieldData ['decimalvalue']  = $decimalValue;
				$fieldData ['picklistvalue'] = self::getPicklistValue ($adb, $fieldData ['columnname'], $type);
			}

			return $fieldData;
		}

	}
