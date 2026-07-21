<?php
	require_once ('include/platzilla/Objects/Field.php');
	require_once ('include/platzilla/Objects/TableField.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('config.inc.php');
	class TableFieldManager {
		
		/** @var GridViewManager[]|null */
		private static $INSTANCES = null;
		
		/** @var PearDatabase */
		private $adb;
		
		private $masterDataBaseName;
		
		public function __construct (PearDatabase $adb) {
			global $dbconfig;
			$this->adb = $adb;
			$this->masterDataBaseName = isset($dbconfig['db_name']) ? $dbconfig['db_name'] : '';
		}
		
		/**
		 * @param string $tableFieldName
		 * @param string $moduleName
		 */
		public function deleteTableField($tableFieldName, $moduleName) {
			if (empty($tableFieldName) || empty($moduleName)) {
				return;
			}
			$this->adb->pquery ('DELETE FROM vtiger_table_field WHERE tablefieldname=? AND entityname=?', array ($tableFieldName, $moduleName));
		}
		
		/**
		 * @param string $tableFieldName
		 * @param string $moduleName
		 *
		 * @return TableField[]|null
		 * @throws Exception
		 */
		public function fetchTableFieldConfig ($tableFieldName, $moduleName) {
			if (empty($tableFieldName)) {
				return null;
			}
			
			$result = $this->adb->pquery ('SELECT * FROM vtiger_table_field WHERE entityname=? AND tablefieldname=? ORDER BY sequence', array ($moduleName, $tableFieldName));
			
			if ($this->adb->num_rows ($result) > 0) {
				$tableFields = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$tableFields[] = TableField::getInstance()
						->setActionField ($row ['actionfield'])
						->setActionFieldArray ($row ['actionfield'])
						->setAttributes ($row ['attributes'])
						->setAttributesArray ($row ['attributes'])
						->setDataField ($row ['datafield'])
						->setDefaultValue ($row ['defaultvalue'])
						->setEntityName ($row ['entityname'])
						->setFieldLabel ($row ['fieldlabel'])
						->setFieldLength ($row ['fieldlength'])
						->setFieldName ($row ['fieldname'])
						->setFieldPrecision ($row ['fieldprecision'])
						->setFilterField ($row ['filterfield'])
						->setRelModule ($row ['relmodule'])
						->setSequence ($row ['sequence'])
						->setUiType ($row['uitype'])
						->setTableFieldId ($row ['tablefieldid'])
						->setTableFieldName ($row ['tablefieldname']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($tableFields)) ? $tableFields : null;
		}
		
		/**
		 * @param string $moduleName
		 *
		 * @return TableField[]|null
		 * @throws Exception
		 */
		public function fetchTableFieldByModule ($moduleName) {
			if (empty($moduleName)) {
				return null;
			}
			$result = $this->adb->pquery (
				'SELECT
					f.fieldid,
					f.fieldname
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
			if ($this->adb->num_rows ($result) > 0) {
				$tableFields = array ();
				while ($row = $this->adb->fetchByAssoc ($result,-1, false)) {
					$tableFields[$row ['fieldid']] = $this->fetchTableFieldConfig ($row ['fieldname'], $moduleName);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($tableFields)) ? $tableFields : null;
		}
		
		/**
		 * @param TableField[] $tableFields
		 *
		 * @return mixed
		 * @throws Exception
		 */
		public function saveTableField($tableFields, $mode) {
			if ((empty ($tableFields)) || (!count ($tableFields))) {
				return null;
			}
			
			$moduleName = $tableFields[0]->getEntityName ();
			$fieldName  = $tableFields[0]->getTableFieldName ();
			$result     = $this->adb->pquery (
				'SELECT
						f.fieldid
					  FROM vtiger_field f
					  INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?
					  WHERE f.fieldname=? AND f.uitype=?',
				array ($moduleName, $fieldName, FieldInterface::UI_TYPE_TABLE_FIELD)
			);
			if ($this->adb->num_rows ($result) > 0) {
				if ($mode == 'UPDATE') {
					$this->adb->pquery ('DELETE FROM vtiger_table_field WHERE entityname=? AND tablefieldname=?', array ($moduleName, $fieldName));
				}
				foreach ($tableFields as $tableField) {
					if ((empty ($tableField)) || (!$tableField instanceof TableField)) {
						continue;
					}
					$result = $this->adb->pquery ('SELECT tablefieldid FROM vtiger_table_field WHERE entityname=? AND tablefieldname=? AND fieldname=?', array ($tableField->getEntityName (), $tableField->getTableFieldName (), $tableField->getFieldName()));
					if (($result) && ($this->adb->num_rows ($result) > 0)) {
						$row     = $this->adb->fetchByAssoc ($result, -1, false);
						$fieldId = $row ['tablefieldid'];
						$this->adb->pquery ('DELETE FROM vtiger_table_field WHERE tablefieldid=?', array ($fieldId));
					}
					$this->adb->pquery (
						'INSERT INTO vtiger_table_field (`entityname`, `tablefieldname`, `fieldname`, `fieldlabel`, `fieldlength`, `fieldprecision`, `sequence`, `uitype`, `actionfield`, `relmodule`, `datafield`, `attributes`, `locked`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
						array ($tableField->getEntityName (), $tableField->getTableFieldName (), $tableField->getFieldName (), $tableField->getFieldLabel (), $tableField->getFieldLength (), $tableField->getFieldPrecision (), $tableField->getSequence (), $tableField->getUiType (), $tableField->getActionField (), $tableField->getRelModule (), $tableField->getDataField (), $tableField->getAttributes (), $tableField->getLocked ())
					);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $tableFields;
		}
		
		/**
		 * @param PearDatabase $adb
		 *
		 * @return TableFieldManager|mixed
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
