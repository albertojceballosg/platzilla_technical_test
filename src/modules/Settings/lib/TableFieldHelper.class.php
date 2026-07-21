<?php
	require_once ('include/platzilla/Managers/FieldManager.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/platzilla/Data/TableFieldCheckbox.php');
	require_once ('include/platzilla/Data/TableFieldImport.php');
	require_once ('include/platzilla/Data/TableFieldList.php');
	require_once ('include/platzilla/Objects/TableField.php');
	require_once ('include/platzilla/Managers/TableFieldManager.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	
	abstract class TableFieldHelper {
		
		/**
		 * @param integer $uiType
		 * @param integer $length
		 * @param null|integer $precision
		 *
		 * @return null|string
		 */
		private static function calculateSqlDataType ($uiType, $length, $precision = null) {
			$length = (is_numeric ($length)) && ($length > 0) ? $length : 255;
			if (($uiType === null) || (in_array ($uiType, array (FieldInterface::UI_TYPE_ATTACHMENTS, FieldInterface::UI_TYPE_GRID)))) {
				return null;
			} else if (in_array ($uiType, array (FieldInterface::UI_TYPE_CHECKBOX, FieldInterface::UI_TYPE_CODE, FieldInterface::UI_TYPE_EMAIL, FieldInterface::UI_TYPE_MODULE_REFERENCE, FieldInterface::UI_TYPE_PHONE, FieldInterface::UI_TYPE_PICKLIST, FieldInterface::UI_TYPE_PIPELINE, FieldInterface::UI_TYPE_SKYPE, FieldInterface::UI_TYPE_TEXT, FieldInterface::UI_TYPE_URL))) {
				return "VARCHAR({$length})";
			} else if (in_array ($uiType, array (FieldInterface::UI_TYPE_CREATED_TIME, FieldInterface::UI_TYPE_DATETIME))) {
				return 'DATETIME';
			} else if ($uiType == FieldInterface::UI_TYPE_DATE) {
				return 'DATE';
			} else if ($uiType == FieldInterface::UI_TYPE_TIME) {
				return 'TIME';
			} else if (in_array ($uiType, array (FieldInterface::UI_TYPE_MODIFIED_BY, FieldInterface::UI_TYPE_MULTI_SELECT, FieldInterface::UI_TYPE_TEXTAREA, FieldInterface::UI_TYPE_VIDEO))) {
				return 'TEXT';
			} else if (in_array ($uiType, array (FieldInterface::UI_TYPE_CURRENCY, FieldInterface::UI_TYPE_NUMBER, FieldInterface::UI_TYPE_PERCENTAGE))) {
				return "NUMERIC({$length},{$precision})";
			} else if (in_array ($uiType, array (FieldInterface::UI_TYPE_OWNER, FieldInterface::UI_TYPE_MODIFIED_BY))) {
				return 'INT(19)';
			} else if (in_array ($uiType, array (FieldInterface::UI_TYPE_CALCULATED_LINK))) {
				return 'DECIMAL(20,2)';
			} else if (in_array ($uiType, array (FieldInterface::UI_TYPE_SUMMARY_ROW))) {
				return 'LONGTEXT';
			} else {
				return 'VARCHAR(255)';
			}
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $tableName
		 * @param string $sqlDataType
		 * @param string $field
		 *
		 * @return boolean
		 * @throws DatabaseException
		 * @throws Exception
		 */
		public static function checkForUpdateTable ($adb, $tableName, $sqlDataType, $field) {
			if (empty($tableName) || empty($sqlDataType) || empty($field)) {
				return false;
			}
			$structure   = self::getColumnNameFromTable ($adb, $tableName);
			$columnNames = $structure ['columnname'];
			$columnTypes = $structure ['datatype'];
			$found       = false;
			foreach ($columnNames as $key => $columnName) {
				if ($field == $columnName) {
					$found = true;
					if ($sqlDataType != strtoupper ($columnTypes [$key])) {
						DatabaseUtils::deleteColumnIfExists ($adb, $tableName, $columnName);
						$found = false;
					}
					break;
				}
			}
			return $found;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $tableName
		 *
		 * @return array|null
		 * @throws Exception
		 */
		private static  function getColumnNameFromTable ($adb, $tableName) {
			$result = $adb->pquery ('SELECT COLUMN_NAME AS columnname, COLUMN_TYPE AS datatype FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME=?', array($tableName));
			if ($adb->num_rows ($result) > 0) {
				$columnNames = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$columnNames['columnname'][] = $row['columnname'];
					$columnNames['datatype'][] = $row['datatype'];
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($columnNames) ? $columnNames : null);
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $tableName
		 * @param array $newStructure
		 * @param array $moduleData
		 *
		 * @throws DatabaseException
		 * @throws Exception
		 */
		public static function updateStructureTable ($adb, $tableName, $newStructure, $moduleData) {
			if (empty($newStructure) || !count ($newStructure)) {
				return;
			}
			$structure      = self::getColumnNameFromTable ($adb, $tableName);
			$columnNames    = $structure ['columnname'];
			$newStructure[] = "{$moduleData['moduleName']}tfid";
			$newStructure[] = $moduleData ['fieldTableName'];
			foreach ($columnNames as $key => $columnName) {
				if (!in_array ($columnName, $newStructure)) {
					DatabaseUtils::deleteColumnIfExists ($adb, $tableName, $columnName);
				}
			}
		}
		
		/**
		 * @param $adb
		 * @param string $fieldTableName
		 * @param array $dataTableField
		 * @param string $mode
		 *
		 * @throws Exception
		 */
		public static function buildTableField ($adb, $fieldTableName, $dataTableField, $mode) {
			if (empty($dataTableField ['moduleData'])) {
				throw new Exception ('No se ha recibido la información de las columnas');
			}
			$fields        = $dataTableField ['moduleData'];
			$appearance    = $dataTableField ['appearance'];
			$summryRow     = $dataTableField ['summaryRow'];
			$operationRow  = $dataTableField ['operationRow'];
			$isLocked      = $dataTableField ['locked'];
			$rowRelModule  = 0;
			$rowList       = 0;
			$rowAppearance = 0;
			$sequence      = 1;
			foreach ($fields as $field) {
				if ($field ['type'] == FieldInterface::UI_TYPE_MODULE_REFERENCE) {
					$action = array (
						'relatedmodule' => $dataTableField['linkages']['relatedmodule'][$rowRelModule],
						'fieldname'     => $dataTableField['linkages']['fieldname'][$rowRelModule],
						'import'        => $dataTableField['linkages'][$field['name']],
					);
					
					$rowRelModule++;
				} else if ($field ['type'] == FieldInterface::UI_TYPE_PICKLIST) {
					$action = array(
						'stringvalues' => $dataTableField['linkages']['list'][$rowList],
						'list'         => $dataTableField['linkages']['list'][$field['name']],
					);
					
					$rowList++;
				} else if ($field ['type'] == FieldInterface::UI_TYPE_CHECKBOX) {
					$action = $dataTableField['activation'][$field['name']];
				}
				
				$attribute = array ('width' => $appearance['width'][$rowAppearance], 'style' => $appearance['style'][$rowAppearance]);
				$tableFields[] = TableField::getInstance()
					->setEntityName ($dataTableField['moduleName'])
					->setTableFieldName ($fieldTableName)
					->setFieldName ($field['name'])
					->setFieldLabel ($field['label'])
					->setFieldLength ($field['length'])
					->setFieldPrecision ($field['precision'])
					->setSequence ($sequence)
					->setUiType ($field['type'])
					->setDataField ($field['globalpicklist'])
					->setRelModule ($field['referencedmodulename'])
					->setActionField ($action)
					->setAttributes ($attribute)
					->setLocked ($isLocked);
				$rowAppearance++;
				$sequence++;
				unset ($action);
				unset($resultAction);
				unset($attribute);
			}
			if (count($summryRow) && !empty($summryRow)) {
				$action = base64_encode (serialize ($summryRow));
				$tableFields[] = TableField::getInstance()
					->setEntityName ($dataTableField['moduleName'])
					->setTableFieldName ($fieldTableName)
					->setFieldName ('summaryRow')
					->setFieldLabel ('Fila resumen')
					->setFieldLength (0)
					->setFieldPrecision (0)
					->setSequence ($sequence)
					->setUiType (FieldInterface::UI_TYPE_SUMMARY_ROW)
					->setDataField ('')
					->setRelModule ('')
					->setActionField ($action)
					->setAttributes ('')
					->setLocked ($isLocked);
				$sequence++;
				unset ($action);
			}
			if (count($operationRow) && !empty($operationRow)) {
				$action = base64_encode (serialize ($operationRow));
				
				$tableFields[] = TableField::getInstance()
					->setEntityName ($dataTableField['moduleName'])
					->setTableFieldName ($fieldTableName)
					->setFieldName ('operationRow')
					->setFieldLabel ('Operaciones')
					->setFieldLength (0)
					->setFieldPrecision (0)
					->setSequence ($sequence)
					->setUiType (FieldInterface::UI_TYPE_OPERATION_ROW)
					->setDataField ('')
					->setRelModule ('')
					->setActionField ($action)
					->setAttributes ('')
					->setLocked ($isLocked);
			}
			
			TableFieldManager::getInstance ($adb)->saveTableField ($tableFields, $mode);
		}
		
		/**
		 * @param PearDatabase$adb
		 * @param array $moduleData
		 * @param string $tableName
		 * @param array $summaryRow
		 * @param null|string $mode
		 *
		 * @throws DatabaseException
		 * @throws Exception
		 */
		public static function buildTableFieldData ($adb, $moduleData, $tableName, $summaryRow, $mode = null) {
			if (empty($moduleData) || !count ($moduleData)) {
				throw new Exception ('No se ha recibido la información de las columnas');
			}
			$structure = array ();
			$fields = $moduleData['moduleData'];
			foreach ($fields as $field) {
				if (empty ($field['name'])) {
					continue;
				}
				$sqlDataType = self::calculateSqlDataType ($field['type'], $field['length'], $field['precision']);
				if ($mode == 'UPDATE') {
					$found = self::checkForUpdateTable ($adb, $tableName, $sqlDataType, $field['name']);
					if ($found) {
						$structure[] = $field['name'];
						if ($field['type'] == FieldInterface::UI_TYPE_MODULE_REFERENCE) {
							$structure[] = "{$field['name']}id";
						}
						continue;
					}
				}
				DatabaseUtils::addColumnIfNotExists ($adb, $tableName, $field['name'], $sqlDataType);
				$structure[] = $field['name'];
				if ($field['type'] == FieldInterface::UI_TYPE_MODULE_REFERENCE) {
					$culumnaName = "{$field['name']}id";
					$sqlDataType = self::calculateSqlDataType (FieldInterface::UI_TYPE_OWNER, 19);
					DatabaseUtils::addColumnIfNotExists ($adb, $tableName, $culumnaName, $sqlDataType);
					$structure[] = $culumnaName;
				}
			}
			if (!empty($summaryRow) && count ($summaryRow)) {
				$sqlDataType = self::calculateSqlDataType (FieldInterface::UI_TYPE_SUMMARY_ROW, 12, 2);
				DatabaseUtils::addColumnIfNotExists ($adb, $tableName, 'summaryRow', $sqlDataType);
				$structure[] = 'summaryRow';
			}
			if ($mode == 'UPDATE') {
				self::updateStructureTable ($adb, $tableName, $structure, $moduleData);
			}
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $tableName
		 * @param string $idColumnName
		 *
		 * @throws Exception
		 */
		public static function changeKeyToIndex ($adb, $tableName, $idColumnName) {
			if (empty ($tableName) || empty ($idColumnName)) {
				throw new Exception ('Upoos! Imposible cambiar index');
			}
			$adb->query ("ALTER TABLE `{$tableName}` DROP PRIMARY KEY, ADD INDEX (`{$idColumnName}`) USING BTREE;");
		}
		
		/**
		 * @param PearDatabase$adb
		 * @param string $moduleName
		 * @param string $relModule
		 *
		 * @return mixed
		 */
		public static function getRelatedFields ($adb, $moduleName, $relModule) {
			if (empty ($moduleName) || empty ($relModule)) {
				return null;
			}
			$result = $adb->pquery (
				'SELECT fieldname, tablefieldname  FROM vtiger_table_field WHERE entityname=? AND relmodule=? AND uitype=?',
				array ($moduleName, $relModule, 10)
			);
			if (($result) && ($adb->num_rows ($result) > 0)) {
				$row   = $adb->fetchByAssoc ($result, -1, false);
				$field [0] = $row['tablefieldname'];
				$field [1] = $row ['fieldname'].'id';
				
				
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($field)) ? $field : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $tabId
		 * @param string $fieldName
		 *
		 * @return string|null
		 */
		public static function getTableName ($adb, $tabId, $fieldName) {
			if (empty ($tabId) || empty ($fieldName)) {
				return null;
			}
			$result = $adb->pquery (
				'SELECT tablename FROM vtiger_field WHERE tabid=? AND fieldname=? AND uitype=?',
				array ($tabId, $fieldName, 2208)
			);
			if (($result) && ($adb->num_rows ($result) > 0)) {
				$row   = $adb->fetchByAssoc ($result, -1, false);
				$tableName = $row ['tablename'];
				
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($tableName)) ? $tableName : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param string $fieldName
		 *
		 * @return string
		 * @throws Exception
		 */
		public static function getVtigerTableName($adb, $moduleName, $fieldName = null) {
			if (empty($fieldName)) {
				$result = $adb->query ('SELECT (id +10) AS fieldid FROM vtiger_field_seq WHERE 1');
				if ($adb->num_rows ($result) > 0) {
					$row = $adb->fetchByAssoc ($result, -1, false);
					$nextInsertID = $row ['fieldid'];
				}
				$tableName = "vtiger_{$moduleName}_ft{$nextInsertID}";
			} else {
				$result = $adb->pquery (
					'SELECT f.tablename  FROM vtiger_field f INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=? WHERE f.fieldname=?',
					array ($moduleName, $fieldName)
				);
				$tableName  = null;
				if (($result) && ($adb->num_rows ($result) > 0)) {
					$row       = $adb->fetchByAssoc ($result, -1, false);
					$tableName = $row ['tablename'];
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $tableName;
		}
		
		/**
		 * @param string $string
		 *
		 * @return mixed
		 */
		public static function sanitizeString ($string) {
			$string = str_replace (
				array ('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
				array ('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
				$string
			);
			$string = str_replace (
				array ('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
				array ('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
				$string
			);
			$string = str_replace (
				array ('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
				array ('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
				$string
			);
			$string = str_replace (
				array ('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
				array ('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
				$string
			);
			$string = str_replace (
				array ('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
				array ('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
				$string
			);
			$string = str_replace (
				array ('ñ', 'Ñ', 'ç', 'Ç'),
				array ('n', 'N', 'c', 'C'),
				$string
			);
			$string = str_replace (
				array ('·', '$', '%', '&', '/', '(', ')', '?', '¡', '¿', '[', '^', ']', '+', '}', '{', '¨', '´', '>', '< ', ';', ',', ':', '.', ' )', ' '),
				'_',
				$string
			);
			$string = trim (strtolower ($string));
			return $string;
		}
		
	}
