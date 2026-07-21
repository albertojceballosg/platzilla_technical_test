<?php
	require_once ('include/platzilla/Exceptions/FieldDependencyException.php');
	require_once ('include/platzilla/Managers/PicklistManager.php');
	require_once ('include/platzilla/Objects/Field.php');
	require_once ('include/platzilla/Objects/FieldDependency.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	class FieldDependencyManager {
		/** @var FieldDependencyManager[]|null */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * @param FieldDependency $dependency
		 *
		 * @throws FieldDependencyException
		 */
		private function validate ($dependency) {
			if ((empty ($dependency)) || (!($dependency instanceof FieldDependency))) {
				throw new FieldDependencyException (FieldDependencyException::ERROR_FIELD_DEPENDENCY_EMPTY);
			}

			$dependency->validate ();

			$result = $this->adb->pquery ('SELECT * FROM vtiger_tab WHERE name=?', array ($dependency->getModuleName ()));
			if ($this->adb->num_rows ($result) == 0) {
				$e = new FieldDependencyException (FieldDependencyException::ERROR_FIELD_DEPENDENCY_INVALID_MODULE_NAME);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($e)) {
				throw $e;
			}

			$result = $this->adb->pquery (
				'SELECT f.* FROM vtiger_field f INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=? WHERE f.fieldname=?',
				array ($dependency->getModuleName (), $dependency->getSourceFieldName ())
			);
			if ($this->adb->num_rows ($result) == 0) {
				DatabaseUtils::closeResult ($result);
				$result = null;
				throw new FieldDependencyException (FieldDependencyException::ERROR_FIELD_DEPENDENCY_INVALID_SOURCE_FIELD_NAME);
			}

			$row = $this->adb->fetchByAssoc ($result, -1, false);
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (!in_array ($row ['uitype'], array (Field::UI_TYPE_GLOBAL_PICKLIST, Field::UI_TYPE_MULTI_SELECT, Field::UI_TYPE_PICKLIST, Field::UI_TYPE_PIPELINE))) {
				throw new FieldDependencyException (FieldDependencyException::ERROR_FIELD_DEPENDENCY_INVALID_SOURCE_FIELD_UITYPE);
			}

			$result = $this->adb->pquery (
				'SELECT f.* FROM vtiger_field f INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=? WHERE f.fieldname=?',
				array ($dependency->getModuleName (), $dependency->getTargetFieldName ())
			);
			if ($this->adb->num_rows ($result) == 0) {
				$e = new FieldDependencyException (FieldDependencyException::ERROR_FIELD_DEPENDENCY_INVALID_TARGET_FIELD_NAME);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($e)) {
				throw $e;
			}
		}

		/**
		 * @param FieldDependency $dependency
		 */
		public function deleteDependency ($dependency) {
			if ((empty ($dependency)) || (!($dependency instanceof FieldDependency))) {
				return;
			}

			$moduleName      = $dependency->getModuleName ();
			$sourceFieldName = $dependency->getSourceFieldName ();
			$targetFieldName = $dependency->getTargetFieldName ();
			if ((empty ($moduleName)) || (empty ($sourceFieldName)) || (empty ($targetFieldName))) {
				return;
			}

			$sourceFieldValue = $dependency->getSourceFieldValue ();
			if (empty ($sourceFieldValue)) {
				$whereClause = '(sourcefieldvalue IS NULL OR sourcefieldvalue=?)';
				$arguments = array ('');
			} else {
				$whereClause = 'sourcefieldvalue=?';
				$arguments = array ($sourceFieldValue);
			}

			$this->adb->pquery (
				"DELETE FROM vtiger_fielddependencies WHERE modulename=? AND sourcefieldname=? AND {$whereClause} AND targetfieldname=?",
				array_merge (array ($dependency->getModuleName (), $dependency->getSourceFieldName (), $dependency->getTargetFieldName ()), $arguments)
			);
		}

		/**
		 * @param string $moduleName
		 * @param string $fieldName
		 */
		public function deleteDependenciesBySourceFieldName ($moduleName, $fieldName) {
			if ((empty ($moduleName)) || (empty ($fieldName))) {
				return;
			}

			$this->adb->pquery (
				'DELETE FROM vtiger_fielddependencies WHERE modulename=? AND sourcefieldname=?',
				array ($moduleName, $fieldName)
			);
		}

		/**
		 * @param string $moduleName
		 * @param string $fieldName
		 */
		public function deleteDependenciesByTargetFieldName ($moduleName, $fieldName) {
			if ((empty ($moduleName)) || (empty ($fieldName))) {
				return;
			}

			$this->adb->pquery (
				'DELETE FROM vtiger_fielddependencies WHERE modulename=? AND targetfieldname=?',
				array ($moduleName, $fieldName)
			);
		}

		/**
		 * @param string $moduleName
		 *
		 * @return FieldDependency[]|null
		 */
		public function fetchDependencies ($moduleName) {
			$result = $this->adb->pquery (
				'SELECT * FROM vtiger_fielddependencies WHERE modulename=?',
				array ($moduleName)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$dependencies = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$dependencies [] = FieldDependency::getInstance ()
						->setModuleName ($moduleName)
						->setSourceFieldName ($row ['sourcefieldname'])
						->setSourceFieldValue ($row ['sourcefieldvalue'])
						->setTargetFieldName ($row ['targetfieldname'])
						->setTargetFieldVisibility (intval ($row ['targetfieldvisibility']));
				}
			} else {
				$dependencies = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $dependencies;
		}

		/**
		 * @param string $moduleName
		 * @param string $fieldName
		 *
		 * @return FieldDependency[]|null
		 */
		public function fetchDependenciesBySourceFieldName ($moduleName, $fieldName) {
			$result = $this->adb->pquery (
				'SELECT * FROM vtiger_fielddependencies WHERE modulename=? AND sourcefieldname=?',
				array ($moduleName, $fieldName)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$dependencies = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$dependencies [] = FieldDependency::getInstance ()
						->setModuleName ($moduleName)
						->setSourceFieldName ($fieldName)
						->setSourceFieldValue ($row ['sourcefieldvalue'])
						->setTargetFieldName ($row ['targetfieldname'])
						->setTargetFieldVisibility (intval ($row ['targetfieldvisibility']));
				}
			} else {
				$dependencies = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $dependencies;
		}

		/**
		 * @param FieldDependency $dependency
		 *
		 * @return FieldDependency|null
		 * @throws FieldDependencyException
		 */
		public function saveDependency ($dependency) {
			if ((empty ($dependency)) || (!($dependency instanceof FieldDependency))) {
				return null;
			}

			$this->validate ($dependency);
			$result = $this->adb->pquery (
				'SELECT * FROM vtiger_fielddependencies WHERE modulename=? AND sourcefieldname=? AND sourcefieldvalue=? AND targetfieldname=?',
				array ($dependency->getModuleName (), $dependency->getSourceFieldName (), $dependency->getSourceFieldValue (), $dependency->getTargetFieldName ())
			);
			if ($this->adb->num_rows ($result) == 0) {
				$this->adb->pquery (
					'INSERT INTO vtiger_fielddependencies (modulename, sourcefieldname, sourcefieldvalue, targetfieldname, targetfieldvisibility) VALUES (?, ?, ?, ?, ?)',
					array ($dependency->getModuleName (), $dependency->getSourceFieldName (), $dependency->getSourceFieldValue (), $dependency->getTargetFieldName (), $dependency->getTargetFieldVisibility ())
				);
			} else {
				$this->adb->pquery (
					'UPDATE vtiger_fielddependencies SET targetfieldvisibility=? WHERE modulename=? AND sourcefieldname=? AND sourcefieldvalue=? AND targetfieldname=?',
					array ($dependency->getTargetFieldVisibility (), $dependency->getModuleName (), $dependency->getSourceFieldName (), $dependency->getSourceFieldValue (), $dependency->getTargetFieldName ())
				);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $dependency;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return FieldDependencyManager
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
