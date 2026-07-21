<?php
	require_once ('include/platzilla/Managers/AppFieldManager.php');
	require_once ('include/platzilla/Managers/ChartManager.php');
	require_once ('include/platzilla/Managers/FieldDependencyManager.php');
	require_once ('include/platzilla/Managers/FieldModuleReferenceManager.php');
	require_once ('include/platzilla/Managers/FieldProfileManager.php');
	require_once ('include/platzilla/Managers/FieldValidationManager.php');
	require_once ('include/platzilla/Managers/GlobalPicklistManager.php');
	require_once ('include/platzilla/Managers/GridManager.php');
	require_once ('include/platzilla/Managers/KanbanViewManager.php');
	require_once ('include/platzilla/Managers/PicklistManager.php');
	require_once ('include/platzilla/Managers/PipelineManager.php');
	require_once ('include/platzilla/Managers/ReportsManager.php');
	require_once ('include/platzilla/Managers/ViewManager.php');
	require_once ('include/platzilla/Objects/Field.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	class FieldManager {
		
		/** @var FieldManager[]|null */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * @param Field $field
		 * @param string $tableName
		 * @param string $columnName
		 *
		 * @throws DatabaseException
		 */
		private function addColumn ($field, $tableName, $columnName) {
			if ((empty ($field)) || (!($field instanceof Field)) || (in_array ($field->getUiType (), array (Field::UI_TYPE_ATTACHMENTS, Field::UI_TYPE_GRID)))) {
				return;
			}

			DatabaseUtils::addColumnIfNotExists ($this->adb, $tableName, $columnName, $field->getSqlDataType (), !$field->isMandatory ());
		}

		/**
		 * @param Field $field
		 */
		private function createDefaultProfiles ($field) {
			if ((empty ($field)) || (!($field instanceof Field))) {
				return;
			}

			FieldProfileManager::getInstance ($this->adb)->createDefaultProfiles ($field->getModuleName (), $field->getName ());
		}

		/**
		 * @param Field $field
		 */
		private function deleteProfiles ($field) {
			if ((empty ($field)) || (!($field instanceof Field))) {
				return;
			}

			FieldProfileManager::getInstance ($this->adb)->deleteProfiles ($field->getModuleName (), $field->getName ());
		}

		/**
		 * @param Field $field
		 * @param integer $oldUiType
		 */
		private function deleteGrid ($field, $oldUiType) {
			if ((empty ($field)) || (!($field instanceof Field)) || ($oldUiType != Field::UI_TYPE_GRID)) {
				return;
			}

			GridManager::getInstance ($this->adb)->deleteGrid ($field->getGrid ());
			$field->setGrid (null);
		}

		/**
		 * @param Field $field
		 * @param integer $oldUiType
		 */
		private function deleteModuleReferences ($field, $oldUiType) {
			if ((empty ($field)) || (!($field instanceof Field)) || ($oldUiType != Field::UI_TYPE_MODULE_REFERENCE)) {
				return;
			}

			FieldModuleReferenceManager::getInstance ($this->adb)->deleteReferencesByFieldName ($field->getModuleName (), $field->getName ());
			$field->setModuleReferences (null);
		}

		/**
		 * @param Field $field
		 * @param integer $oldUiType
		 */
		private function deletePicklist ($field, $oldUiType) {
			if ((empty ($field)) || (!($field instanceof Field)) || (!in_array ($oldUiType, array (Field::UI_TYPE_MULTI_SELECT, Field::UI_TYPE_PICKLIST)))) {
				return;
			}

			PicklistManager::getInstance ($this->adb)->deletePicklist ($field->getPicklist ());
			FieldDependencyManager::getInstance ($this->adb)->deleteDependenciesBySourceFieldName ($field->getModuleName (), $field->getName ());
			$field->setPicklist (null)->setDependencies (null);
		}

		/**
		 * @param Field $field
		 * @param integer $oldUiType
		 */
		private function deletePipeline ($field, $oldUiType) {
			if ((empty ($field)) || (!($field instanceof Field)) || (!in_array ($oldUiType, array (Field::UI_TYPE_PIPELINE)))) {
				return;
			}

			PipelineManager::getInstance ($this->adb)->deletePipeline ($field->getPipeline ());
			FieldDependencyManager::getInstance ($this->adb)->deleteDependenciesBySourceFieldName ($field->getModuleName (), $field->getName ());
			$field->setPipeline (null)->setDependencies (null);
		}

		/**
		 * @param Field $field
		 * @param boolean $includeDeleted
		 */
		private function fetchInnerObjectsForUiType ($field, $includeDeleted = false) {
			$fieldName  = $field->getName ();
			$moduleName = $field->getModuleName ();
			$uiType     = $field->getUiType ();
			if ($uiType == Field::UI_TYPE_GRID) {
				$field->setGrid (GridManager::getInstance ($this->adb)->fetchGridByName ($moduleName, $fieldName));
			} else if (in_array ($uiType, array (Field::UI_TYPE_MODULE_RECORDS, Field::UI_TYPE_MODULE_REFERENCE))) {
				$field->setModuleReferences (FieldModuleReferenceManager::getInstance ($this->adb)->fetchReferences ($moduleName, $fieldName));
			} else if (in_array ($uiType, array (Field::UI_TYPE_MULTI_SELECT, Field::UI_TYPE_PICKLIST))) {
				$field->setDependencies (FieldDependencyManager::getInstance ($this->adb)->fetchDependenciesBySourceFieldName ($moduleName, $fieldName));
				$field->setPicklist (PicklistManager::getInstance ($this->adb)->fetchPicklistByName ($fieldName, $includeDeleted));
			} else if ($uiType == Field::UI_TYPE_GLOBAL_PICKLIST) {
				$field->setDependencies (FieldDependencyManager::getInstance ($this->adb)->fetchDependenciesBySourceFieldName ($moduleName, $fieldName));
				$field->setPicklist (GlobalPicklistManager::getInstance ($this->adb)->fetchPicklistByName ($fieldName));
			} else if ($uiType == Field::UI_TYPE_PIPELINE) {
				$field->setDependencies (FieldDependencyManager::getInstance ($this->adb)->fetchDependenciesBySourceFieldName ($moduleName, $fieldName));
				$field->setPipeline (PipelineManager::getInstance ($this->adb)->fetchPipeline ($moduleName, $fieldName));
			}
		}

		/**
		 * @param string $moduleName
		 * @param Field[] $existingFields
		 *
		 * @return Field[]
		 */
		private function fetchMissingDeletedFields ($moduleName, $existingFields) {
			if (empty ($moduleName)) {
				return array ();
			}

			$existingFieldNames = array ();
			foreach ($existingFields as $existingField) {
				$existingFieldNames [] = $existingField->getName ();
			}

			$fields = array ();
			$result = $this->adb->pquery ('SELECT * FROM vtiger_deletedelements WHERE elementtype=? AND modulename=?', array ('field', $moduleName));
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					/** @var Field $field */
					$field = unserialize ($row ['serializedobject']);
					if (!in_array ($field->getName (), $existingFieldNames)) {
						$field->setDeleted (true);
						$fields [] = $field;
					}
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $fields;
		}

		/**
		 * @param Field $field
		 *
		 * @return integer
		 */
		private function getSequenceNumber ($field) {
			$sequence = isset ($field) ? $field->getSequence () : null;
			if ((!empty ($sequence)) && (is_numeric ($sequence))) {
				$sequence = intval ($sequence);
			} else {
				$result = $this->adb->pquery (
					'SELECT MAX(f.sequence) AS maxsequence FROM vtiger_field f INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=? WHERE f.block=?',
					array ($field->getModuleName (), $field->getBlockId ())
				);
				if ($this->adb->num_rows ($result) == 0) {
					$sequence = 1;
				} else {
					$row      = $this->adb->fetchByAssoc ($result, -1, false);
					$sequence = (intval ($row ['maxsequence']) + 1);
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
			}
			return $sequence;
		}

		/**
		 * @param Field $field
		 *
		 * @return integer
		 */
		private function getQuickCreateSequenceNumber ($field) {
			$quickCreate         = $field->getQuickCreate ();
			$quickCreateSequence = $field->getQuickCreateSequence ();
			if ((!isset ($quickCreate)) || ($quickCreate == Field::QUICK_CREATE_DISABLED)) {
				$sequence = null;
			} else if ((!empty ($quickCreateSequence)) && (is_numeric ($quickCreateSequence))) {
				$sequence = intval ($quickCreateSequence);
			} else {
				$result = $this->adb->pquery (
					'SELECT MAX(f.quickcreatesequence) AS maxsequence FROM vtiger_field f INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?',
					array ($field->getModuleName ())
				);
				if ($this->adb->num_rows ($result) == 0) {
					$sequence = 1;
				} else {
					$row      = $this->adb->fetchByAssoc ($result, -1, false);
					$sequence = (intval ($row ['maxsequence']) + 1);
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
			}
			return $sequence;
		}
		
		/**
		 * @param Field $field
		 *
		 * @throws AppFieldException
		 */
		private function saveAppField ($field) {
			if (empty($field->getAppField ())) {
				return;
			}
			AppFieldManager::getInstance ($this->adb)->saveAppField ($field);
		}
		
		/**
		 * @param Field $field
		 */
		private function saveGrid ($field) {
			if ($field->getUiType () != Field::UI_TYPE_GRID) {
				$field->setGrid (null);
			} else {
				GridManager::getInstance ($this->adb)->saveGrid ($field->getGrid ());
			}
		}

		/**
		 * @param Field $field
		 * @param boolean $ignoreLock
		 */
		private function saveModuleReferences ($field, $ignoreLock) {
			$fmrm       = FieldModuleReferenceManager::getInstance ($this->adb);
			$references = $field->getModuleReferences ();
			if (!in_array ($field->getUiType (), array (Field::UI_TYPE_MODULE_RECORDS, Field::UI_TYPE_MODULE_REFERENCE))) {
				$fmrm->deleteReferencesByFieldName ($field->getModuleName (), $field->getName ());
				$field->setModuleReferences (null);
			} else if (!empty ($references)) {
				foreach ($references as $reference) {
					$reference->setFieldName ($field->getName ())
						->setModuleName ($field->getModuleName ());
				}
				$fmrm->saveReferences ($references, $ignoreLock);
			}
		}

		/**
		 * @param Field $field
		 * @param boolean $ignoreLock
		 */
		private function savePicklist ($field, $ignoreLock) {
			if (!in_array ($field->getUiType (), array (Field::UI_TYPE_MULTI_SELECT, Field::UI_TYPE_PICKLIST))) {
				$field->setPicklist (null);
			} else {
				PicklistManager::getInstance ($this->adb)->savePicklist ($field->getPicklist (), $ignoreLock);
			}
		}

		/**
		 * @param Field $field
		 */
		private function savePipeline ($field) {
			if (!in_array ($field->getUiType (), array (Field::UI_TYPE_PIPELINE))) {
				$field->setPipeline (null);
			} else {
				PipelineManager::getInstance ($this->adb)->savePipeline ($field->getPipeline ());
			}
		}

		/**
		 * @param Field $field
		 * @param boolean $ignoreLock
		 */
		private function saveValidations ($field, $ignoreLock) {
			$fvm = FieldValidationManager::getInstance ($this->adb);
			$fvm->deleteValidationsByFieldName ($field->getModuleName (), $field->getName (), $ignoreLock);
			$validations = $field->getValidations ();
			if (empty ($validations)) {
				return;
			}

			foreach ($validations as $validation) {
				$validation->setFieldName ($field->getName ())
					->setModuleName ($field->getModuleName ())
					->setTableName ($field->getTableName ());
				$fvm->saveValidation ($validation, $ignoreLock);
			}
		}

		/**
		 * @param Field $field
		 * @param string $tableName
		 * @param string $columnName
		 *
		 * @throws DatabaseException
		 */
		private function updateColumn ($field, $tableName, $columnName) {
			if ((empty ($field)) || (!($field instanceof Field)) || (in_array ($field->getUiType (), array (Field::UI_TYPE_ATTACHMENTS, Field::UI_TYPE_GRID)))) {
				return;
			}

			DatabaseUtils::updateColumnIfExists ($this->adb, $tableName, $columnName, $field->getSqlDataType ());
		}

		/**
		 * @param Field $field
		 *
		 * @throws FieldException
		 */
		private function validate ($field) {
			if ((empty ($field)) || (!($field instanceof Field))) {
				throw new FieldException (FieldException::ERROR_FIELD_EMPTY);
			} else if ($field->isDeleted ()) {
				return;
			}

			$field->validate ();

			$tableName = $field->getTableName ();
			if (empty ($tableName)) {
				throw new FieldException (FieldException::ERROR_FIELD_EMPTY_TABLE_NAME);
			}

			if (!DatabaseUtils::checkIfTableExists ($this->adb, $field->getTableName ())) {
				throw new FieldException (FieldException::ERROR_FIELD_INVALID_TABLE_NAME);
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_tab WHERE name=?', array ($field->getModuleName ()));
			if ($this->adb->num_rows ($result) == 0) {
				DatabaseUtils::closeResult ($result);
				$result = null;
				throw new FieldException (FieldException::ERROR_FIELD_INVALID_MODULE_NAME);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			$result = $this->adb->pquery (
				'SELECT b.* FROM vtiger_blocks b INNER JOIN vtiger_tab t ON t.tabid=b.tabid AND t.name=? WHERE b.blockid=?',
				array ($field->getModuleName (), $field->getBlockId ())
			);
			if ($this->adb->num_rows ($result) == 0) {
				DatabaseUtils::closeResult ($result);
				$result = null;
				throw new FieldException (FieldException::ERROR_FIELD_INVALID_BLOCK_ID);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			$this->validateModuleReferences ($field);
			$this->validatePicklist ($field);
			$this->validatePipeline ($field);
		}

		/**
		 * @param Field $field
		 *
		 * @throws FieldException
		 */
		private function validateModuleReferences ($field) {
			if ($field->getUiType () != Field::UI_TYPE_MODULE_REFERENCE) {
				return;
			}

			$references = $field->getModuleReferences ();
			foreach ($references as $reference) {
				if ((empty ($reference)) || (!($reference instanceof FieldModuleReference))) {
					throw new FieldException (FieldException::ERROR_FIELD_INVALID_MODULE_REFERENCE);
				}
				$reference->validate ();
			}
		}

		/**
		 * @param Field $field
		 *
		 * @throws FieldException
		 */
		private function validatePicklist ($field) {
			if (!in_array ($field->getUiType (), array (Field::UI_TYPE_MULTI_SELECT, Field::UI_TYPE_PICKLIST))) {
				return;
			}

			$field->getPicklist ()->validate ();
		}

		/**
		 * @param Field $field
		 */
		private function validatePipeline ($field) {
			if (!in_array ($field->getUiType (), array (Field::UI_TYPE_PIPELINE))) {
				return;
			}

			$field->getPipeline ()->validate ();
		}

		/**
		 * @param Field $field
		 */
		public function deleteField ($field) {
			if ((empty ($field)) || (!($field instanceof Field))) {
				return;
			}

			$moduleName = $field->getModuleName ();
			$identifier = $field->getName ();
			$this->adb->startTransaction ();
			if ((!empty ($moduleName)) && (!empty ($identifier))) {
				// TODO: Hasta que los grids estén estables sólo se guardarán los encabezados del campo en la tabla vtiger_deletedelements
				$fieldHeaders = Field::getInstance ()
					->setBlockId ($field->getBlockId ())
					->setColumnName ($field->getColumnName ())
					->setDefaultValue ($field->getDefaultValue ())
					->setDisplayType ($field->getDisplayType ())
					->setGeneratedType ($field->getGeneratedType ())
					->setId ($field->getId ())
					->setLabel ($field->getLabel ())
					->setMandatory ($field->isMandatory ())
					->setMassEditable ($field->getMassEditable ())
					->setModuleName ($field->getModuleName ())
					->setName ($field->getName ())
					->setPresence ($field->getPresence ())
					->setQuickCreate ($field->getQuickCreate ())
					->setQuickCreateSequence ($field->getQuickCreateSequence ())
					->setReadOnly ($field->getReadOnly ())
					->setSequence ($field->getSequence ())
					->setTableName ($field->getTableName ())
					->setUiType ($field->getUiType (), $field->getLength (), $field->getPrecision ());
				$this->adb->pquery ('DELETE FROM vtiger_deletedelements WHERE elementtype=? AND modulename=? AND identifier=?', array ('field', $moduleName, $identifier));
				$this->adb->pquery ('INSERT INTO vtiger_deletedelements (elementtype, modulename, identifier, deletedon, serializedobject) VALUES (?, ?, ?, ?, ?)', array ('field', $moduleName, $identifier, date ('Y-m-d h:i:s'), serialize ($fieldHeaders)));
			}
			ChartManager::getInstance ($this->adb)->deleteFieldFromCharts ($field);
			ViewManager::getInstance ($this->adb)->deleteFieldFromViews ($field);
			ReportsManager::getInstance ($this->adb)->deleteFieldFromReports ($field);
			KanbanViewManager::getInstance ($this->adb)->deleteFieldFromViews ($field);
			$this->deleteProfiles ($field);
			$this->deleteGrid ($field, $field->getUiType ());
			$this->deleteModuleReferences ($field, $field->getUiType ());
			$fdm = FieldDependencyManager::getInstance ($this->adb);
			$fdm->deleteDependenciesBySourceFieldName ($field->getModuleName (), $field->getName ());
			$fdm->deleteDependenciesByTargetFieldName ($field->getModuleName (), $field->getName ());
			FieldValidationManager::getInstance ($this->adb)->deleteValidationsByFieldName ($field->getModuleName (), $field->getName ());
			$this->adb->pquery ('DELETE FROM vtiger_field WHERE fieldid=?', array ($field->getId ()));
			$this->deletePicklist ($field, $field->getUiType ());
			$this->deletePipeline ($field, $field->getUiType ());
			if ($field->getTableName () != 'vtiger_crmentity') {
				DatabaseUtils::deleteColumnIfExists ($this->adb, $field->getTableName (), $field->getColumnName ());
			}
			$this->adb->completeTransaction ();
		}

		/**
		 * @param integer $blockId
		 */
		public function deleteFieldsByBlockId ($blockId) {
			$fields = $this->fetchFieldsByBlockId ($blockId);
			if (empty ($fields)) {
				return;
			}

			foreach ($fields as $field) {
				$this->adb->startTransaction ();
				ChartManager::getInstance ($this->adb)->deleteFieldFromCharts ($field);
				ViewManager::getInstance ($this->adb)->deleteFieldFromViews ($field);
				ReportsManager::getInstance ($this->adb)->deleteFieldFromReports ($field);
				KanbanViewManager::getInstance ($this->adb)->deleteFieldFromViews ($field);
				$this->deleteProfiles ($field);
				$this->deleteGrid ($field, $field->getUiType ());
				$this->deleteModuleReferences ($field, $field->getUiType ());
				$fdm = FieldDependencyManager::getInstance ($this->adb);
				$fdm->deleteDependenciesBySourceFieldName ($field->getModuleName (), $field->getName ());
				$fdm->deleteDependenciesByTargetFieldName ($field->getModuleName (), $field->getName ());
				FieldValidationManager::getInstance ($this->adb)->deleteValidationsByFieldName ($field->getModuleName (), $field->getName ());
				$this->adb->pquery ('DELETE FROM vtiger_field WHERE fieldid=?', array ($field->getId ()));
				$this->deletePicklist ($field, $field->getUiType ());
				$this->deletePipeline ($field, $field->getUiType ());
				if ($field->getTableName () != 'vtiger_crmentity') {
					DatabaseUtils::deleteColumnIfExists ($this->adb, $field->getTableName (), $field->getColumnName ());
				}
				$this->adb->completeTransaction ();
			}
		}

		/**
		 * @param $fieldId
		 *
		 * @return Field|null
		 */
		public function fetchFieldById ($fieldId) {
			$result = $this->adb->pquery (
				'SELECT f.*, t.name AS modulename FROM vtiger_field f INNER JOIN vtiger_tab t ON t.tabid=f.tabid WHERE f.fieldid=?',
				array ($fieldId)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row   = $this->adb->fetchByAssoc ($result, -1, false);
				$field = Field::getInstance ($row ['typeofdata'])
					->setBlockId (intval ($row ['block']))
					->setCalculationName ($row ['paradicional'])
					->setColumnName ($row ['columnname'])
					->setDefaultValue ($row ['defaultvalue'])
					->setDisplayType (intval ($row ['displaytype']))
					->setGeneratedType (intval ($row ['generatedtype']))
					->setId (intval ($fieldId))
					->setLabel ($row ['fieldlabel'])
					->setLocked ($row ['locked'] == 1)
					->setMassEditable (intval ($row ['masseditable']))
					->setModuleName ($row ['modulename'])
					->setName ($row ['fieldname'])
					->setPresence (intval ($row ['presence']))
					->setQuickCreate (intval ($row ['quickcreate']))
					->setQuickCreateSequence (isset ($row ['quickcreatesequence']) ? intval ($row ['quickcreatesequence']) : null)
					->setReadOnly (intval ($row ['readonly']))
					->setSequence (intval ($row ['sequence']))
					->setTableName ($row ['tablename'])
					->setUiType ($row ['uitype'])
					->setValidations (FieldValidationManager::getInstance ($this->adb)->fetchValidationsByFieldName ($row ['modulename'], $row ['fieldname']));
				$this->fetchInnerObjectsForUiType ($field);
			} else {
				$field = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $field;
		}

		/**
		 * @param string $moduleName
		 * @param string $fieldName
		 * @param boolean $headersOnly
		 *
		 * @return Field|null
		 */
		public function fetchFieldByName ($moduleName, $fieldName, $headersOnly = false) {
			$result = $this->adb->pquery (
				'SELECT f.*, t.name AS modulename FROM vtiger_field f INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=? WHERE f.fieldname=?',
				array ($moduleName, $fieldName)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row   = $this->adb->fetchByAssoc ($result, -1, false);
				$field = Field::getInstance ($row ['typeofdata'])
					->setBlockId (intval ($row ['block']))
					->setCalculationName ($row ['paradicional'])
					->setColumnName ($row ['columnname'])
					->setDefaultValue ($row ['defaultvalue'])
					->setDisplayType (intval ($row ['displaytype']))
					->setGeneratedType (intval ($row ['generatedtype']))
					->setId (intval ($row ['fieldid']))
					->setLabel ($row ['fieldlabel'])
					->setLocked ($row ['locked'] == 1)
					->setMassEditable (intval ($row ['masseditable']))
					->setModuleName ($row ['modulename'])
					->setName ($row ['fieldname'])
					->setPresence (intval ($row ['presence']))
					->setQuickCreate (intval ($row ['quickcreate']))
					->setQuickCreateSequence (isset ($row ['quickcreatesequence']) ? intval ($row ['quickcreatesequence']) : null)
					->setReadOnly (intval ($row ['readonly']))
					->setSequence (intval ($row ['sequence']))
					->setTableName ($row ['tablename'])
					->setUiType ($row ['uitype'])
					->setValidations (!$headersOnly ? FieldValidationManager::getInstance ($this->adb)->fetchValidationsByFieldName ($moduleName, $fieldName) : null);
				if (!$headersOnly) {
					$this->fetchInnerObjectsForUiType ($field);
				}
			} else {
				$field = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $field;
		}

		/**
		 * @param string $moduleName
		 *
		 * @return Field[]|null
		 */
		public function fetchFieldHeaders ($moduleName) {
			$result = $this->adb->pquery (
				'SELECT f.* FROM vtiger_field f INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?',
				array ($moduleName)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$fields = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$fieldName = $row ['fieldname'];
					$field     = Field::getInstance ($row ['typeofdata'])
						->setBlockId (intval ($row ['block']))
						->setCalculationName ($row ['paradicional'])
						->setColumnName ($row ['columnname'])
						->setDefaultValue ($row ['defaultvalue'])
						->setDisplayType (intval ($row ['displaytype']))
						->setGeneratedType (intval ($row ['generatedtype']))
						->setId (intval ($row ['fieldid']))
						->setLabel ($row ['fieldlabel'])
						->setLocked ($row ['locked'] == 1)
						->setMassEditable (intval ($row ['masseditable']))
						->setModuleName ($moduleName)
						->setName ($fieldName)
						->setPresence (intval ($row ['presence']))
						->setQuickCreate (intval ($row ['quickcreate']))
						->setQuickCreateSequence (isset ($row ['quickcreatesequence']) ? intval ($row ['quickcreatesequence']) : null)
						->setReadOnly (intval ($row ['readonly']))
						->setSequence (intval ($row ['sequence']))
						->setTableName ($row ['tablename'])
						->setUiType ($row ['uitype']);
					$fields [] = $field;
				}
			} else {
				$fields = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $fields;
		}

		/**
		 * @param string $moduleName
		 *
		 * @return Field[]|null
		 */
		public function fetchFields ($moduleName) {
			$result = $this->adb->pquery (
				'SELECT f.*, t.name AS modulename FROM vtiger_field f INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?',
				array ($moduleName)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$fields = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$fieldName = $row ['fieldname'];
					$field     = Field::getInstance ($row ['typeofdata'])
						->setBlockId (intval ($row ['block']))
						->setCalculationName ($row ['paradicional'])
						->setColumnName ($row ['columnname'])
						->setDefaultValue ($row ['defaultvalue'])
						->setDisplayType (intval ($row ['displaytype']))
						->setGeneratedType (intval ($row ['generatedtype']))
						->setId (intval ($row ['fieldid']))
						->setLabel ($row ['fieldlabel'])
						->setLocked ($row ['locked'] == 1)
						->setMassEditable (intval ($row ['masseditable']))
						->setModuleName ($row ['modulename'])
						->setName ($fieldName)
						->setPresence (intval ($row ['presence']))
						->setQuickCreate (intval ($row ['quickcreate']))
						->setQuickCreateSequence (isset ($row ['quickcreatesequence']) ? intval ($row ['quickcreatesequence']) : null)
						->setReadOnly (intval ($row ['readonly']))
						->setSequence (intval ($row ['sequence']))
						->setTableName ($row ['tablename'])
						->setUiType ($row ['uitype'])
						->setValidations (FieldValidationManager::getInstance ($this->adb)->fetchValidationsByFieldName ($moduleName, $fieldName));
					$this->fetchInnerObjectsForUiType ($field);
					$fields [] = $field;
				}
			} else {
				$fields = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $fields;
		}

		/**
		 * @param string $moduleName
		 * @param integer $uiType
		 * @param boolean $headersOnly
		 *
		 * @return Field[]|null
		 * @throws Exception
		 */
		public function fetchFieldsByUiType ($moduleName, $uiType, $headersOnly = false) {
			$result = $this->adb->pquery (
				'SELECT f.* FROM vtiger_field f INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=? WHERE f.uitype=?',
				array ($moduleName, $uiType)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$fields = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$fieldName = $row ['fieldname'];
					$field     = Field::getInstance ($row ['typeofdata'])
						->setBlockId (intval ($row ['block']))
						->setCalculationName ($row ['paradicional'])
						->setColumnName ($row ['columnname'])
						->setDefaultValue ($row ['defaultvalue'])
						->setDisplayType (intval ($row ['displaytype']))
						->setGeneratedType (intval ($row ['generatedtype']))
						->setId (intval ($row ['fieldid']))
						->setLabel ($row ['fieldlabel'])
						->setLocked ($row ['locked'] == 1)
						->setMassEditable (intval ($row ['masseditable']))
						->setModuleName ($moduleName)
						->setName ($fieldName)
						->setPresence (intval ($row ['presence']))
						->setQuickCreate (intval ($row ['quickcreate']))
						->setQuickCreateSequence (isset ($row ['quickcreatesequence']) ? intval ($row ['quickcreatesequence']) : null)
						->setReadOnly (intval ($row ['readonly']))
						->setSequence (intval ($row ['sequence']))
						->setTableName ($row ['tablename'])
						->setUiType ($uiType)
						->setValidations (FieldValidationManager::getInstance ($this->adb)->fetchValidationsByFieldName ($moduleName, $fieldName));
					if (!$headersOnly) {
						$this->fetchInnerObjectsForUiType ($field);
					}
					$fields [] = $field;
				}
			} else {
				$fields = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $fields;
		}

		/**
		 * @param integer $blockId
		 * @param boolean $includeDeleted
		 *
		 * @return Field[]|null
		 */
		public function fetchFieldsByBlockId ($blockId, $includeDeleted = false) {
			if (empty ($blockId)) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT
					f.*,
					t.name AS modulename
				FROM
					vtiger_field f
					INNER JOIN vtiger_blocks b ON b.blockid=f.block AND b.blockid=?
					INNER JOIN vtiger_tab t ON t.tabid=b.tabid
				ORDER BY
					f.sequence',
				array ($blockId)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row        = $this->adb->fetchByAssoc ($result, -1, false);
				$moduleName = $row ['modulename'];
				$fields     = array ();
				do {
					$field = Field::getInstance ($row ['typeofdata'])
						->setBlockId (intval ($blockId))
						->setCalculationName ($row ['paradicional'])
						->setColumnName ($row ['columnname'])
						->setDefaultValue ($row ['defaultvalue'])
						->setDisplayType (intval ($row ['displaytype']))
						->setGeneratedType (intval ($row ['generatedtype']))
						->setId (intval ($row ['fieldid']))
						->setLabel ($row ['fieldlabel'])
						->setLocked ($row ['locked'] == 1)
						->setMassEditable (intval ($row ['masseditable']))
						->setModuleName ($row ['modulename'])
						->setName ($row ['fieldname'])
						->setPresence (intval ($row ['presence']))
						->setQuickCreate (intval ($row ['quickcreate']))
						->setQuickCreateSequence (isset ($row ['quickcreatesequence']) ? intval ($row ['quickcreatesequence']) : null)
						->setReadOnly (intval ($row ['readonly']))
						->setSequence (intval ($row ['sequence']))
						->setTableName ($row ['tablename'])
						->setUiType ($row ['uitype'])
						->setValidations (FieldValidationManager::getInstance ($this->adb)->fetchValidationsByFieldName ($row ['modulename'], $row ['fieldname']));
					$this->fetchInnerObjectsForUiType ($field, $includeDeleted);
					$fields [] = $field;
				} while ($row = $this->adb->fetchByAssoc ($result, -1, false));

				if ($includeDeleted) {
					$deletedFields = $this->fetchMissingDeletedFields ($moduleName, $fields);
				} else {
					$deletedFields = array ();
				}
				$fields = array_merge ($fields, $deletedFields);
			} else {
				$fields = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $fields;
		}
		
		/**
		 * @param Field $field
		 * @param boolean $ignoreLock
		 *
		 * @return Field|null
		 * @throws AppFieldException
		 * @throws DatabaseException
		 * @throws Exception
		 * @throws FieldException
		 */
		public function saveField ($field, $ignoreLock = true) {
			$this->validate ($field);
			$isDeleted = $field->isDeleted ();
			if ($isDeleted) {
				return $field;
			}

			$tableName           = strtolower ($field->getTableName ());
			$columnName          = strtolower ($field->getColumnName ());
			$sequence            = $this->getSequenceNumber ($field);
			$quickCreateSequence = $this->getQuickCreateSequenceNumber ($field);
			$this->adb->startTransaction ();
			$result = $this->adb->pquery (
				'SELECT f.* FROM vtiger_field f INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=? WHERE f.fieldname=?',
				array ($field->getModuleName (), $field->getName ())
			);
			if (($result) && ($this->adb->num_rows ($result) > 0)) {
				$row      = $this->adb->fetchByAssoc ($result, -1, false);
				$fieldId  = intval ($row ['fieldid']);
				$isLocked = ($row ['locked'] == 1);
				$uiType   = $row ['uitype'];
			} else {
				$fieldId  = null;
				$isLocked = false;
				$uiType   = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			if (empty ($fieldId)) {
				$fieldId = ($field->getId ()) ? $field->getId () : $this->adb->getUniqueID ('vtiger_field');
				$this->adb->pquery (
					'INSERT INTO vtiger_field (tabid, fieldid, columnname, tablename, generatedtype, uitype, fieldname, fieldlabel, readonly, presence, defaultvalue, maximumlength, sequence, block, displaytype, typeofdata, quickcreate, quickcreatesequence, info_type, masseditable, helpinfo, paradicional, locked)
					VALUES ((SELECT tabid FROM vtiger_tab WHERE name=? LIMIT 1), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
					array ($field->getModuleName (), $fieldId, $columnName, $tableName, $field->getGeneratedType (), $field->getUiType (), $field->getName (), $field->getLabel (), $field->getReadOnly (), $field->getPresence (), $field->getDefaultValue (), 100, $sequence, $field->getBlockId (), $field->getDisplayType (), $field->getTypeOfData (), $field->getQuickCreate (), $quickCreateSequence, 'BAS', $field->getMassEditable (), null, $field->getCalculationName (), $field->isLocked ())
				);
				$this->addColumn ($field, $tableName, $columnName);
				$this->createDefaultProfiles ($field);
				$field->setId ($this->adb->getLastInsertID ());
			} else if (($ignoreLock) || (!$isLocked)) {
				if ($field->getUiType () != $uiType) {
					$this->deleteGrid ($field, $uiType);
					$this->deleteModuleReferences ($field, $uiType);
				}
				// TODO: cuando se repare el módulo Calendar, quitar esta mierda
				if (!in_array ($field->getModuleName (), array ('Calendar', 'Events'))) {
					$this->updateColumn ($field, $tableName, $columnName);
				}
				$this->adb->pquery (
					'UPDATE vtiger_field SET generatedtype=?, uitype=?, fieldname=?, fieldlabel=?, readonly=?, presence=?, defaultvalue=?, sequence=?, block=?, displaytype=?, typeofdata=?, quickcreate=?, quickcreatesequence=?, masseditable=?, paradicional=?, locked=? WHERE fieldid=?',
					array ($field->getGeneratedType (), $field->getUiType (), $field->getName (), $field->getLabel (), $field->getReadOnly (), $field->getPresence (), $field->getDefaultValue (), $sequence, $field->getBlockId (), $field->getDisplayType (), $field->getTypeOfData (), $field->getQuickCreate (), $quickCreateSequence, $field->getMassEditable (), $field->getCalculationName (), $field->isLocked (), $fieldId)
				);
				if ($field->getUiType () != $uiType) {
					// Eliminar un picklist requiere que ningún campo lo esté usando. Por ello se actualiza el campo antes de intentar eliminar el picklist
					$this->deletePicklist ($field, $uiType);
				}
				$field->setId ($fieldId);
			}
			
			$this->saveAppField ($field);
			$this->saveGrid ($field);
			$this->savePicklist ($field, $ignoreLock);
			$this->savePipeline ($field);
			$this->saveModuleReferences ($field, $ignoreLock);
			$this->saveValidations ($field, $ignoreLock);
			$this->adb->completeTransaction ();
			return $field;
		}

		/**
		 * @param Field $field
		 *
		 * @return Field
		 * @throws FieldException
		 */
		public function updateFieldHeader ($field) {
			if ((empty ($field)) || (!($field instanceof Field))) {
				throw new FieldException (FieldException::ERROR_FIELD_EMPTY);
			}

			$isDeleted = $field->isDeleted ();
			if ($isDeleted) {
				return $field;
			}

			$result = $this->adb->pquery (
				'SELECT f.* FROM vtiger_field f INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=? WHERE f.fieldname=?',
				array ($field->getModuleName (), $field->getName ())
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row      = $this->adb->fetchByAssoc ($result, -1, false);
				$fieldId  = intval ($row ['fieldid']);
				$sequence = $this->getSequenceNumber ($field);
				$this->adb->pquery (
					'UPDATE vtiger_field SET generatedtype=?, fieldlabel=?, readonly=?, presence=?, defaultvalue=?, sequence=?, displaytype=?, quickcreate=?, masseditable=?, locked=? WHERE fieldid=?',
					array ($field->getGeneratedType (), $field->getLabel (), $field->getReadOnly (), $field->getPresence (), $field->getDefaultValue (), $sequence, $field->getDisplayType (), $field->getQuickCreate (), $field->getMassEditable (), $field->isLocked (), $fieldId)
				);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $field;
		}

		/**
		 * @param Field $field
		 *
		 * @throws FieldException
		 */
		public function validateField ($field) {
			$this->validate ($field);
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return FieldManager
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
