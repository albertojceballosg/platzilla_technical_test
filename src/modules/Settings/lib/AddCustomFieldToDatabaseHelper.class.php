<?php
	require_once ('include/utils/CommonUtils.php');

	abstract class AddCustomFieldToDatabaseHelper {

		private static function createPickList (PearDatabase $adb, $arguments) {
			$type = $arguments ['type'];
			if (($type != 'Picklist') && ($type != 'MultiSelectCombo')) {
				return;
			}

			// Creating the PickList Table and Populating Values
			$columnName = $adb->sql_escape_string ($arguments ['columnName']);
			$adb->query (
				"CREATE TABLE vtiger_{$columnName} (
					{$columnName}id INT(19) NOT NULL AUTO_INCREMENT,
					{$columnName} VARCHAR(200) NOT NULL,
					presence INT(1) NOT NULL DEFAULT '1',
					picklist_valueid INT(19) NOT NULL DEFAULT '0',
					PRIMARY KEY ({$columnName}id)
				)"
			);

			// Adding a new picklist value in the picklist table
			$mode = $arguments ['mode'];
			if ($mode != 'edit') {
				$adb->pquery ('INSERT INTO vtiger_picklist VALUES (?, ?)', array ($adb->getUniqueID ('vtiger_picklist'), $columnName));
			}

			$result         = $adb->pquery ('SELECT picklistid FROM vtiger_picklist WHERE name=?', array ($columnName));
			$pickListId     = $adb->query_result ($result, 0, 'picklistid');
			$pickListValues = explode ("\n", $arguments ['pickList']);
			$n              = count ($pickListValues);
			for ($i = 0; $i < $n; $i++) {
				$pickListValues [ $i ] = trim (from_html ($pickListValues [ $i ]));
				if ($pickListValues [ $i ] == '') {
					continue;
				}

				$found  = false;
				$result = $adb->query ("SELECT $columnName FROM vtiger_{$columnName}");
				while ($row = $adb->fetchByAssoc ($result)) {
					if ($pickListValues [ $i ] == $row [ $columnName ]) {
						$found = true;
						break;
					}
				}
				if (!$found) {
					$id = $adb->getUniqueID ('vtiger_picklistvalues');
					$adb->pquery (
						"INSERT INTO vtiger_{$columnName} VALUES (?, ?, ?, ?)",
						array ($adb->getUniqueID ("vtiger_{$columnName}"), $pickListValues [ $i ], 1, $id)
					);
				}

				$result  = $adb->pquery ("SELECT picklist_valueid FROM vtiger_{$columnName} where {$columnName}=?", array ($pickListValues [ $i ]));
				$valueId = $adb->query_result ($result, 0, 'picklist_valueid');
				$adb->pquery (
					'INSERT INTO vtiger_role2picklist (roleid, picklistvalueid, picklistid, sortid) SELECT roleid, ?, ?, ? FROM vtiger_role',
					array ($valueId, $pickListId, $i)
				);
			}
		}

		private static function getDateTimeFieldUiData ($type) {
			switch ($type) {
				case 'Date':
					$uiType       = 5;
					$databaseType = 'D';
					$uiCheckData  = 'D~O';
					break;
				case 'Time':
					$uiType       = 14;
					$databaseType = 'TIME';
					$uiCheckData  = 'T~O';
					break;
				default:
					$uiType       = '';
					$databaseType = '';
					$uiCheckData  = '';
					break;
			}

			return array (
				'uiType'       => $uiType,
				'databaseType' => $databaseType,
				'uiCheckData'  => $uiCheckData,
			);
		}

		private static function getNumericFieldUiData ($type, $length, $decimal) {
			switch ($type) {
				case 'Number':
					// this may sound ridiculous passing decimal but that is the way adodb wants
					$uiType         = 7;
					$databaseLength = ($length + $decimal + 1);
					$databaseType   = "N({$databaseLength}.{$decimal})";
					$uiCheckData    = "N~O~{$length},{$decimal}";
					break;
				case 'Percent':
					$uiType       = 9;
					$databaseType = 'N(5.2)';
					$uiCheckData  = 'N~O~2~2';
					break;
				case 'Currency':
					$uiType         = 71;
					$databaseLength = ($length + $decimal + 1);
					$databaseType   = "N({$databaseLength}.{$decimal})";
					$uiCheckData    = "N~O~{$length},{$decimal}";
					break;
				default:
					$uiType       = '';
					$databaseType = '';
					$uiCheckData  = '';
					break;
			}

			return array (
				'uiType'       => $uiType,
				'databaseType' => $databaseType,
				'uiCheckData'  => $uiCheckData,
			);
		}

		private static function getSelectionFieldUiData ($type) {
			switch ($type) {
				case 'Picklist':
					$uiType       = 15;
					$databaseType = 'C(255) default ()';
					$uiCheckData  = 'V~O';
					break;
				case 'Checkbox':
					$uiType       = 56;
					$databaseType = 'C(3) default 0';
					$uiCheckData  = 'C~O';
					break;
				case 'MultiSelectCombo':
					$uiType       = 33;
					$databaseType = 'X';
					$uiCheckData  = 'V~O';
					break;
				default:
					$uiType       = '';
					$databaseType = '';
					$uiCheckData  = '';
					break;
			}

			return array (
				'uiType'       => $uiType,
				'databaseType' => $databaseType,
				'uiCheckData'  => $uiCheckData,
			);
		}

		private static function getTextFieldUiData ($type, $length) {
			switch ($type) {
				case 'Text':
					$uiType       = 1;
					$databaseType = "C({$length}) default ()";
					$uiCheckData  = "V~O~LE~{$length}";
					break;
				case 'Email':
					$uiType       = 13;
					$databaseType = 'C(50) default ()';
					$uiCheckData  = 'E~O';
					break;
				case 'Phone':
					$uiType       = 11;
					$databaseType = 'C(30) default ()';
					$uiCheckData  = 'V~O';
					break;
				case 'URL':
					$uiType       = 17;
					$databaseType = 'C(255) default ()';
					$uiCheckData  = 'V~O';
					break;
				case 'TextArea':
					$uiType       = 21;
					$databaseType = 'X';
					$uiCheckData  = 'V~O';
					break;
				case 'Skype':
					$uiType       = 85;
					$databaseType = 'C(255) default ()';
					$uiCheckData  = 'V~O';
					break;
				default:
					$uiType       = '';
					$databaseType = '';
					$uiCheckData  = '';
					break;
			}

			return array (
				'uiType'       => $uiType,
				'databaseType' => $databaseType,
				'uiCheckData'  => $uiCheckData,
			);
		}

		public static function getTabId ($module, $activityType) {
			if (!$module) {
				return null;
			}

			if (($module != 'Calendar') || (!$activityType)) {
				return getTabid ($module);
			}

			if ($activityType == 'E') {
				return '16';
			}
			if ($activityType == 'T') {
				return '9';
			}
			return getTabid ($module);
		}

		public static function isFieldRegistered (PearDatabase $adb, $module, $tabId, $label, $fieldId) {
			if ($module == 'Calendar') {
				$questionMarks = '?, ?';
				$parameters    = array ('9', '16');
			} else {
				$questionMarks = '?';
				$parameters    = array ($tabId);
			}

			$sql = "SELECT * FROM vtiger_field WHERE tabid IN ($questionMarks) AND fieldlabel=?";
			$parameters [] = $label;

			if ($fieldId) {
				$sql .= ' AND fieldid<>?';
				$parameters [] = $fieldId;
			}

			$result = $adb->pquery ($sql, $parameters);
			return ($adb->num_rows ($result) > 0);
		}

		public static function getTableName ($module) {
			$tableName = '';
			if ($module == 'Calendar') {
				$tableName = 'vtiger_activitycf';
			} else if ($module != '') {
				require_once ('data/CRMEntity.php');
				$entity = CRMEntity::getInstance ($module);
				if (isset ($entity->customFieldTable)) {
					$tableName = $entity->customFieldTable [0];
				} else {
					$tableName = strtolower ("vtiger_{$module}cf");
				}
			}
			return $tableName;
		}

		public static function getUiData ($type, $length, $decimal) {
			if (in_array ($type, array ('Text', 'Email', 'Phone', 'URL', 'TextArea', 'Skype'))) {
				return self::getTextFieldUiData ($type, $length);
			}
			if (in_array ($type, array ('Date', 'Time'))) {
				return self::getDateTimeFieldUiData ($type);
			}
			if (in_array ($type, array ('Number', 'Percent', 'Currency'))) {
				return self::getNumericFieldUiData ($type, $length, $decimal);
			}
			if (in_array ($type, array ('Picklist', 'Checkbox', 'MultiSelectCombo'))) {
				return self::getSelectionFieldUiData ($type);
			}

			return array (
				'uiType'       => '',
				'databaseType' => '',
				'uiCheckData'  => '',
			);
		}

		public static function updateFields (PearDatabase $adb, $arguments) {
			$blockId = $arguments ['blockId'];
			if (!is_numeric ($blockId)) {
				return;
			}

			$fieldId     = $arguments ['fieldId'];
			$label       = $arguments ['label'];
			$mode        = $arguments ['mode'];
			$uiCheckData = $arguments ['uiCheckData'];
			if (($mode == 'edit') && ($fieldId)) {
				$adb->pquery ('UPDATE vtiger_field SET fieldlabel=?, typeofdata=? WHERE fieldid=?', array ($label, $uiCheckData, $fieldId));
				return;
			}

			if (!$fieldId) {
				return;
			}

			$columnName   = $arguments ['columnName'];
			$databaseType = $arguments ['databaseType'];
			$module       = $arguments ['module'];
			$sequence     = $arguments ['sequence'];
			$tabId        = $arguments ['tabId'];
			$tableName    = $arguments ['tableName'];
			$uiType       = $arguments ['uiType'];
			$adb->pquery (
				'INSERT INTO vtiger_field (tabid, fieldid, columnname, tablename, generatedtype, uitype, fieldname, fieldlabel, readonly, presence, defaultvalue, maximumlength, sequence, block, displaytype, typeofdata, quickcreate, quickcreatesequence, info_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
				array ($tabId, $fieldId, $columnName, $tableName, 2, $uiType, $columnName, $label, 0, 0, '', 100, $sequence, $blockId, 1, $uiCheckData, 1, 0, 'BAS')
			);
			$adb->alterTable ($tableName, "$columnName $databaseType", 'Add_Column');

			//Inserting values into vtiger_profile2field vtiger_tables
			$adb->pquery (
				'INSERT INTO vtiger_profile2field (profileid, tabid, fieldid, visible, readonly) SELECT profileid, ?, ?, 0, 0 FROM vtiger_profile',
				array ($tabId, $fieldId)
			);

			//Inserting values into def_org vtiger_tables
			$adb->pquery ('INSERT INTO vtiger_def_org_field VALUES (?, ?, ?, ?)', array ($tabId, $fieldId, 0, 1));

			// Creating the PickList Table and Populating Values
			self::createPickList ($adb, $arguments);

			//Inserting into LeadMapping table - Jaguar
			if (($module == 'Leads') && (!$fieldId)) {
				$adb->pquery ('INSERT INTO vtiger_convertleadmapping (leadfid) VALUES (?)', array ($fieldId));
			}
		}

	}
