<?php
	require_once ('include/platzilla/Exceptions/FieldValidationException.php');
	require_once ('include/platzilla/Objects/FieldValidation.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	/**
	 * Class FieldValidationManager
	 */
	class FieldValidationManager {
		/** @var FieldValidationManager[]|null */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * @param FieldValidation $validation
		 *
		 * @throws FieldValidationException
		 */
		private function validate ($validation) {
			if ((empty ($validation)) || (!($validation instanceof FieldValidation))) {
				throw new FieldValidationException (FieldValidationException::ERROR_FIELD_VALIDATION_EMPTY);
			}

			$validation->validate ();

			$result = $this->adb->pquery ('SELECT * FROM vtiger_tab WHERE name=?', array ($validation->getModuleName ()));
			if ($this->adb->num_rows ($result) == 0) {
				$e = new FieldValidationException (FieldValidationException::ERROR_FIELD_VALIDATION_INVALID_MODULE_NAME);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($e)) {
				throw $e;
			}

			$result = $this->adb->pquery (
				'SELECT f.* FROM vtiger_field f INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=? WHERE f.fieldname=?',
				array ($validation->getModuleName (), $validation->getFieldName ())
			);
			if ($this->adb->num_rows ($result) == 0) {
				$e = new FieldValidationException (FieldValidationException::ERROR_FIELD_VALIDATION_INVALID_FIELD_NAME);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($e)) {
				throw $e;
			}
		}

		/**
		 * @param FieldValidation $validation
		 */
		public function deleteValidation ($validation) {
			if ((empty ($validation)) || (!($validation instanceof FieldValidation))) {
				return;
			}

			$fieldName  = $validation->getFieldName ();
			$moduleName = $validation->getModuleName ();
			$type       = $validation->getType ();
			if ((empty ($fieldName)) || (empty ($moduleName)) || (empty ($type))) {
				return;
			}

			$this->adb->pquery (
				'DELETE
					fv
				FROM
					vtiger_field_validation fv
					INNER JOIN vtiger_tab t ON t.tabid=fv.tabid AND t.name=?
				WHERE
					fv.fieldname=? AND
					fv.validationtype=?',
				array ($moduleName, $fieldName, $type)
			);
		}

		/**
		 * @param string $moduleName
		 * @param string $fieldName
		 * @param boolean $ignoreLock
		 */
		public function deleteValidationsByFieldName ($moduleName, $fieldName, $ignoreLock = true) {
			if ((empty ($moduleName)) || (empty ($fieldName))) {
				return;
			}

			if (!$ignoreLock) {
				$whereClause = 'AND fv.locked=0';
			} else {
				$whereClause = '';
			}

			$this->adb->pquery (
				"DELETE
					fv
				FROM
					vtiger_field_validation fv
					INNER JOIN vtiger_tab t ON t.tabid=fv.tabid AND t.name=?
				WHERE
					fv.fieldname=?
					{$whereClause}",
				array ($moduleName, $fieldName)
			);
		}

		/**
		 * @param string $moduleName
		 * @param string $fieldName
		 *
		 * @return FieldValidation[]|null
		 */
		public function fetchValidationsByFieldName ($moduleName, $fieldName) {
			$result = $this->adb->pquery (
				'SELECT
					fv.initialvalue,
					fv.maximumvalue,
					fv.tablename,
					fv.validationtype
				FROM
					vtiger_field_validation fv
					INNER JOIN vtiger_tab t ON t.tabid=fv.tabid AND t.name=?
				WHERE
					fv.fieldname=?',
				array ($moduleName, $fieldName)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$validations = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$validations [] = FieldValidation::getInstance ()
						->setFieldName ($fieldName)
						->setInitialValue ($row ['initialvalue'])
						->setLocked (isset($row ['locked']) ? $row ['locked'] == 1 : false)
						->setMaximumValue ($row ['maximumvalue'])
						->setModuleName ($moduleName)
						->setTableName ($row ['tablename'])
						->setType ($row ['validationtype']);
				}
			} else {
				$validations = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $validations;
		}

		/**
		 * @param FieldValidation $validation
		 * @param boolean $ignoreLock
		 *
		 * @return FieldValidation|null
		 * @throws FieldValidationException
		 */
		public function saveValidation ($validation, $ignoreLock = true) {
			if ((empty ($validation)) || (!($validation instanceof FieldValidation))) {
				return null;
			}

			$this->validate ($validation);

			$result = $this->adb->pquery (
				'SELECT
					fv.*
				FROM
					vtiger_field_validation fv
					INNER JOIN vtiger_tab t ON t.tabid=fv.tabid AND t.name=?
				WHERE
					fv.fieldname=? AND
					fv.validationtype=?',
				array ($validation->getModuleName (), $validation->getFieldName (), $validation->getType ())
			);
			if (($result) && ($this->adb->num_rows ($result) > 0)) {
				$row      = $this->adb->fetchByAssoc ($result, -1, false);
				$fieldId  = intval ($row ['fieldid']);
				$isLocked = ($row ['locked'] == 1);
			} else {
				$fieldId  = null;
				$isLocked = false;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			if (empty ($fieldId)) {
				$this->adb->pquery (
					'INSERT INTO vtiger_field_validation (tabid, fieldid, tablename, fieldname, validationtype, initialvalue, maximumvalue, locked)
					SELECT t.tabid, f.fieldid, ?, f.fieldname, ?, ?, ?, ? FROM vtiger_field f INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=? WHERE f.fieldname=? LIMIT 1',
					array ($validation->getTableName (), $validation->getType (), $validation->getInitialValue (), $validation->getMaximumValue (), $validation->isLocked (), $validation->getModuleName (), $validation->getFieldName ())
				);
			} else if (($ignoreLock) || (!$isLocked)) {
				$this->adb->pquery (
					'UPDATE
						vtiger_field_validation fv
						INNER JOIN vtiger_tab t ON t.tabid=fv.tabid AND t.name=?
					SET
						fv.initialvalue=?,
						fv.maximumvalue=?,
						fv.tablename=?,
						fv.locked=?
					WHERE
						fv.fieldname=? AND
						fv.validationtype=?',
					array ($validation->getModuleName (), $validation->getInitialValue (), $validation->getMaximumValue (), $validation->getTableName (), $validation->isLocked (), $validation->getFieldName (), $validation->getType ())
				);
			}

			return $validation;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return FieldValidationManager
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
