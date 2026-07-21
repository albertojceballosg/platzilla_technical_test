<?php
	require_once ('include/fields/DateTimeField.php');
	require_once ('include/platzilla/Managers/TableFieldManager.php');
	require_once ('include/platzilla/Managers/GlobalPicklistManager.php');
	require_once ('include/utils/NumberHelper.class.php');
	class TableFieldUtils {
		
		/** @var TableFieldUtils[]|null */
		private static $INSTANCES = null;
		
		/** @var PearDatabase */
		private $adb;
		
		private $masterDataBaseName;
		
		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
			$this->masterDataBaseName = $dbconfig ['db_name'];
		}
		
		/**
		 * @param string $tableName
		 * @param string $moduleName
		 * @param integer $recordId
		 */
		private function delRecordById ($tableName, $moduleName, $recordId) {
			if (empty($tableName) || empty($moduleName) || !$recordId) {
				return;
			}
			$fieldName = "{$moduleName}tfid";
			$this->adb->query ("DELETE IGNORE FROM {$tableName} WHERE {$fieldName}={$recordId}");
		}
		
		/**
		 * @param array $row
		 * @param string $moduleName
		 * @param integer $recordId
		 *
		 * @return array|null
		 * @throws Exception
		 */
		private function fetchRecordsById ($row, $moduleName, $recordId) {
			if (empty($row)) {
				return null;
			}
			
			$arrColumn = $this->getColumnNameFromTable ($row ['tablename']);
			if (empty ($arrColumn ['columnname'])) {
				return null;
			}
			$columns    = $arrColumn ['columnname'];
			$fieldIndex = "{$moduleName}tfid";
			
			
			$result = $this->adb->query ("SELECT * FROM {$row['tablename']} WHERE {$fieldIndex}={$recordId}");
			if ($this->adb->num_rows ($result) > 0) {
				$resultArray     = array();
				$numberingHelper = NumberHelper::getInstance ($this->adb);
				$gpm             = GlobalPicklistManager::getInstance ($this->adb);
				foreach ($columns as $column) {
					$globalList = $gpm->fetchPicklistByName ($column);
					if (!empty ($globalList)) {
						$resultArray['globallists'][$column] = $globalList->getValues ();
					}
				}
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					foreach ($columns as $key => $column) {
						if (($column == 'summaryRow') && (($row['summaryrow'] != 'NULL')) && ($row['summaryrow'] != '')) {
							$mySummaryRow = json_decode ($row['summaryrow'], true);
							$this->setNumFormatSummaryRow ($mySummaryRow);
							$resultArray[ $column ][] = $mySummaryRow;
						} else if ( $arrColumn['datatype'][$key] == 'decimal') {
							$resultArray[ $column ][] = $numberingHelper->setNumberFormat ($row[ $column ]);
						} else if ( $arrColumn['datatype'][$key] == 'date') {
							// Convertir fecha de BD al formato del usuario
							$dateValue = $row[ $column ];
							if (!empty($dateValue) && $dateValue !== '0000-00-00') {
								$dateValue = DateTimeField::convertToUserFormat($dateValue);
							}
							$resultArray[ $column ][] = $dateValue;
						} else {
							$resultArray[ $column ][] = $row[ $column ];
						}
					}
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($resultArray)) ? $resultArray : null;
		}
		
		/**
		 * @param string $moduleName
		 *
		 * @return mixed
		 */
		private function getTableFields ($moduleName) {
			return $this->adb->pquery (
				'SELECT
					f.fieldid,
					f.fieldname,
					f.tablename
				FROM
					vtiger_field f
				INNER JOIN vtiger_tab t ON t.tabid=f.tabid
				WHERE
					t.name=? AND
					f.uitype=?
				ORDER BY
					f.sequence',
				array ($moduleName, 2208)
			);
		}
		
		/**
		 * @param string $tableName
		 *
		 * @return array|null
		 * @throws Exception
		 */
		private function getColumnNameFromTable ($tableName) {
			$result = $this->adb->pquery (
				'SELECT
						COLUMN_NAME AS columnname,
						DATA_TYPE AS datatype
					  FROM
					  	INFORMATION_SCHEMA.COLUMNS
					  WHERE
					  	TABLE_NAME=? AND
					  	TABLE_SCHEMA=?',
				array($tableName, $this->adb->dbName)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$columnNames = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$columnNames['columnname'][] = $row['columnname'];
					$columnNames['datatype'][] = $row['datatype'];
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($columnNames) ? $columnNames : null);
		}
		
		private function setNumFormatSummaryRow (&$summaryRow) {
			$numberingHelper = NumberHelper::getInstance ($this->adb);
			foreach ($summaryRow as $key => &$value) {
				$value = $numberingHelper->setNumberFormat ($value);
			}
		}
		
		/**
		 * @param string $module
		 * @param integer $recordId
		 * @param string $fieldName
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public function fetchDataTableField ($module, $recordId, $fieldName = '') {
			if (empty($module) || !$recordId) {
				return null;
			}
			$result = $this->getTableFields ($module);
			if ($this->adb->num_rows ($result) > 0) {
				$tableFields = array();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					if (!empty ($fieldName) && $fieldName != $row ['fieldname']) {
						continue;
					}
					$tableFields[ $row['fieldid'] ] = $this->fetchRecordsById ($row, $module, $recordId);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($tableFields)) ? $tableFields : null;
			
		}
		
		/**
		 * @param string $tableName
		 * @param string $fieldName
		 * @param array $arguments
		 *
		 * @SuppressWarnings(PHPMD)
		 * @codingStandardsIgnoreStart
		 * @throws Exception
		 */
		public  function saveTableField ($tableName, $fieldName, $arguments) {
			if (empty($tableName) || empty($fieldName)) {
				return;
			}
			
			$arrColumn = $this->getColumnNameFromTable ($tableName);
			if (empty($arrColumn['columnname'])) {
				return;
			}
			$numberingHelper = NumberHelper::getInstance ($this->adb);
			$columns         = $arrColumn ['columnname'];
			$columnTypes     = $arrColumn ['datatype'];
			$dataFields      = $arguments ['requestData'];
			$module          = $arguments ['module'];
			$recordId        = $arguments ['recordId'];
			$crmIdTable      = "{$module}tfid";
			$totalRecords    = 0;
			$columnString    = join ("`,`", $columns);
			$searchValue     = join (',' , array_fill (0, count ($columns), '?'));
			$dataTableFields = (array) vtlib_purify ($dataFields [ $fieldName ]);
			
			if (in_array ('summaryRow', $columns)) {
				$dataSummary = (array) vtlib_purify ($dataTableFields['summaryRow']);
			}
			
			foreach ($columns as $column) {
				if (in_array ($column, array_keys ($dataTableFields))) {
					$totalRecords = count ($dataTableFields[$column]);
					break;
				}
			}
			
			for ($k = 0; $k < $totalRecords; $k++) {
				$index = 0;
				foreach ($columns as $columnName) {
					if ($columnName == $crmIdTable) {
						$values[] = intval ($recordId);
					} else if ($columnName == $fieldName) {
						$values[] = $module;
					} else if (($columnName == 'summaryRow') && (isset ($dataSummary))) {
						if ($k == 0) {
							$values[] = json_encode ($dataSummary);
						} else {
							$values[] = null;
						}
					} else if (in_array ($columnName, array_keys ($dataTableFields))) {
						if (in_array ($columnTypes[ $index], array ('varchar', 'text'))) {
							$values[] = (string) $dataTableFields [$columnName][$k];
						} else if ($columnTypes[ $index] == 'date') {
							// Convertir fecha del formato del usuario al formato de BD
							$dateValue = $dataTableFields [$columnName][$k];
							if (!empty($dateValue) && $dateValue !== '0000-00-00') {
								$dateValue = DateTimeField::convertToDBFormat($dateValue);
							}
							$values[] = $dateValue;
						} else if ($columnTypes[ $index] == 'int') {
							$values[] = intval ($dataTableFields [$columnName][$k]);
						} else if ($columnTypes[ $index] == 'decimal') {
							$values[] = $numberingHelper->setSaveNumberFormat ($dataTableFields [$columnName][$k]);
						}
					}
					$index++;
				}
				if (count($columns) == count ($values)) {
					$this->adb->pquery ("INSERT INTO {$tableName} (`{$columnString}`) VALUES ({$searchValue})", $values);
				}
				unset($values);
			}
			unset ($_REQUEST[ $fieldName ]);
		}
		//@codingStandardsIgnoreStart
		
		/**
		 * @param array $arguments
		 *
		 * @return null|void
		 * @throws Exception
		 */
		public  function saveTableFields ($arguments) {
			if (empty($arguments)) {
				return;
			}
			
			$mode         = $arguments['requestData']['mode'];
			$recordId     = $arguments['recordId'];
			$module       = $arguments ['module'];
			$recordFields = array_keys ($arguments['requestData']);
			$result       = $this->getTableFields ($module);
			
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					if (!in_array ($row ['fieldname'], $recordFields)) {
						continue;
					}
					if ($mode == 'edit') {
						$this->delRecordById ($row['tablename'], $module, $recordId);
					}
					$this->saveTableField ($row['tablename'], $row ['fieldname'], $arguments);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}
		
		/**
		 * @param PearDatabase $adb
		 *
		 * @return TableFieldUtils|mixed
		 */
		public static function getInstance (PearDatabase $adb) {
			if (self::$INSTANCES === null) {
				self::$INSTANCES = array ();
			}
			if (!isset (self::$INSTANCES [ $adb->dbName ])) {
				self::$INSTANCES [ $adb->dbName ] = new self ($adb);
			}
			return self::$INSTANCES [ $adb->dbName ];
		}
		
	}
