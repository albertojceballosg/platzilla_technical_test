<?php
	require_once ('include/platzilla/Exceptions/FieldModuleReferenceException.php');
	require_once ('include/platzilla/Objects/FieldModuleReference.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	class FieldModuleReferenceManager {
		/** @var FieldModuleReferenceManager[]|null */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * @param string $moduleName
		 *
		 * @return FieldModuleReference[]
		 */
		private function fetchDeletedReferences ($moduleName) {
			if (empty ($moduleName)) {
				return array ();
			}

			$references = array ();
			$result     = $this->adb->pquery ('SELECT * FROM vtiger_deletedelements WHERE elementtype=? AND modulename=?', array ('fieldmodulereference', $moduleName));
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					/** @var CalendarView $view */
					$reference = unserialize ($row ['serializedobject']);
					$reference->setDeleted (true);
					$references [] = $reference;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $references;
		}

		/**
		 * @param integer $referenceId
		 *
		 * @return FieldModuleReferenceFilter[]|null
		 */
		private function fetchFilters ($referenceId) {
			if (empty ($referenceId)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_fieldmodulerel_filters WHERE referenceid=?', array ($referenceId));
			if ($this->adb->num_rows ($result) > 0) {
				$filters = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$filters [] = FieldModuleReferenceFilter::getInstance ()
						->setComparator ($row ['comparator'])
						->setFieldName ($row ['fieldname'])
						->setOperator ($row ['operator'])
						->setSequence (intval ($row ['sequence']))
						->setValue ($row ['value'])
						->setValueModuleName ($row ['valuemodulename'])
						->setValueType ($row ['valuetype']);
				}
			} else {
				$filters = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $filters;
		}

		/**
		 * @param integer $referenceId
		 *
		 * @return FieldModuleReferenceRelationship[]|null
		 */
		private function fetchRelationships ($referenceId) {
			if (empty ($referenceId)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_fieldmodulerel_relationships WHERE referenceid=?', array ($referenceId));
			if ($this->adb->num_rows ($result) > 0) {
				$relationships = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$relationships [] = FieldModuleReferenceRelationship::getInstance ()
						->setFieldName ($row ['fieldname'])
						->setReferencedFieldName ($row ['relfieldname']);
				}
			} else {
				$relationships = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $relationships;
		}

		/**
		 * @param string $moduleName
		 * @param string $fieldName
		 *
		 * @return integer
		 */
		private function getFieldId ($moduleName, $fieldName) {
			$result = $this->adb->pquery (
				'SELECT
					f.*
				FROM
					vtiger_field f
					INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?
				WHERE
					f.fieldname=?',
				array ($moduleName, $fieldName)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row     = $this->adb->fetchByAssoc ($result, -1, false);
				$fieldId = intval ($row ['fieldid']);
			} else {
				$fieldId = 0;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $fieldId;
		}

		/**
		 * @param string $moduleName
		 * @param string $fieldName
		 *
		 * @return integer
		 */
		private function getSequenceNumber ($moduleName, $fieldName) {
			$result = $this->adb->pquery (
				'SELECT
					MAX(fmr.sequence) AS maxsequence
				FROM
					vtiger_fieldmodulerel fmr
					INNER JOIN vtiger_field f ON f.fieldid=fmr.fieldid AND f.fieldname=?
					INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?',
				array ($fieldName, $moduleName)
			);
			if ($this->adb->num_rows ($result) == 0) {
				$sequence = 1;
			} else {
				$row      = $this->adb->fetchByAssoc ($result, -1, false);
				$sequence = (intval ($row ['maxsequence']) + 1);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $sequence;
		}

		/**
		 * @param integer $referenceId
		 * @param FieldModuleReference $reference
		 */
		private function saveFilters ($referenceId, $reference) {
			$this->adb->pquery ('DELETE FROM vtiger_fieldmodulerel_filters WHERE referenceid=?', array ($referenceId));
			$filters = $reference->getFilters ();
			if (empty ($filters)) {
				return;
			}

			foreach ($filters as $filter) {
				$this->adb->pquery (
					'INSERT INTO vtiger_fieldmodulerel_filters (referenceid, sequence, fieldname, comparator, valuetype, valuemodulename, value, operator) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
					array ($referenceId, $filter->getSequence (), $filter->getFieldName (), $filter->getComparator (), $filter->getValueType (), $filter->getValueModuleName (), $filter->getValue (), $filter->getOperator ())
				);
			}
		}

		/**
		 * @param integer $referenceId
		 * @param FieldModuleReference $reference
		 */
		private function saveRelationships ($referenceId, $reference) {
			$this->adb->pquery ('DELETE FROM vtiger_fieldmodulerel_relationships WHERE referenceid=?', array ($referenceId));
			$relationships = $reference->getRelationships ();
			if (empty ($relationships)) {
				return;
			}

			foreach ($relationships as $relationship) {
				$this->adb->pquery (
					'INSERT INTO vtiger_fieldmodulerel_relationships (referenceid, fieldname, relfieldname) VALUES (?, ?, ?)',
					array ($referenceId, $relationship->getFieldName (), $relationship->getReferencedFieldName ())
				);
			}
		}

		/**
		 * @param FieldModuleReference $reference
		 *
		 * @throws FieldModuleReferenceException
		 * @throws FieldModuleReferenceRelationshipException
		 */
		private function validate ($reference) {
			if ((empty ($reference)) || (!($reference instanceof FieldModuleReference))) {
				throw new FieldModuleReferenceException (FieldModuleReferenceException::ERROR_FIELD_MODULE_REFERENCE_EMPTY_REFERENCE);
			}

			$reference->validate ();

			$result = $this->adb->pquery ('SELECT * FROM vtiger_tab WHERE name=?', array ($reference->getModuleName ()));
			if ($this->adb->num_rows ($result) == 0) {
				$e = new FieldModuleReferenceException (FieldModuleReferenceException::ERROR_FIELD_MODULE_REFERENCE_INVALID_MODULE_NAME);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($e)) {
				throw $e;
			}

			$result = $this->adb->pquery (
				'SELECT f.* FROM vtiger_field f INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=? WHERE f.fieldname=? AND f.uitype IN (?, ?)',
				array ($reference->getModuleName (), $reference->getFieldName (), Field::UI_TYPE_MODULE_RECORDS, Field::UI_TYPE_MODULE_REFERENCE)
			);
			if ($this->adb->num_rows ($result) == 0) {
				$e = new FieldModuleReferenceException (FieldModuleReferenceException::ERROR_FIELD_MODULE_REFERENCE_INVALID_FIELD_NAME);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($e)) {
				throw $e;
			}
		}

		/**
		 * @param FieldModuleReference $reference
		 */
		public function deleteReference ($reference) {
			if ((empty ($reference)) || (!($reference instanceof FieldModuleReference))) {
				return;
			}

			$fieldName            = $reference->getFieldName ();
			$moduleName           = $reference->getModuleName ();
			$referencedModuleName = $reference->getReferencedModuleName ();
			if ((empty ($fieldName)) || (empty ($moduleName)) || (empty ($referencedModuleName))) {
				return;
			}

			$identifier = "{$reference->getFieldName ()}-{$reference->getReferencedModuleName ()}";
			if ((!empty ($moduleName)) && (!empty ($identifier))) {
				$this->adb->pquery ('DELETE FROM vtiger_deletedelements WHERE elementtype=? AND modulename=? AND identifier=?', array ('fieldmodulereference', $moduleName, $identifier));
				$this->adb->pquery ('INSERT INTO vtiger_deletedelements (elementtype, modulename, identifier, deletedon, serializedobject) VALUES (?, ?, ?, ?, ?)', array ('fieldmodulereference', $moduleName, $identifier, date ('Y-m-d h:i:s'), serialize ($reference)));
			}

			$this->adb->pquery (
				'DELETE
					fmrf
				FROM
					vtiger_fieldmodulerel_filters fmrf
					INNER JOIN vtiger_fieldmodulerel fmr ON fmr.fieldpk=fmrf.referenceid
					INNER JOIN vtiger_field f ON f.fieldid=fmr.fieldid AND f.fieldname=?
					INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?
				WHERE
					fmr.relmodule=?',
				array ($reference->getFieldName (), $reference->getModuleName (), $reference->getReferencedModuleName ())
			);
			$this->adb->pquery (
				'DELETE
					fmrr
				FROM
					vtiger_fieldmodulerel_relationships fmrr
					INNER JOIN vtiger_fieldmodulerel fmr ON fmr.fieldpk=fmrr.referenceid
					INNER JOIN vtiger_field f ON f.fieldid=fmr.fieldid AND f.fieldname=?
					INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?
				WHERE
					fmr.relmodule=?',
				array ($reference->getFieldName (), $reference->getModuleName (), $reference->getReferencedModuleName ())
			);
			$this->adb->pquery (
				'DELETE
					fmr
				FROM
					vtiger_fieldmodulerel fmr
					INNER JOIN vtiger_field f ON f.fieldid=fmr.fieldid AND f.fieldname=?
					INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?
				WHERE
					fmr.relmodule=?',
				array ($reference->getFieldName (), $reference->getModuleName (), $reference->getReferencedModuleName ())
			);
		}

		/**
		 * @param string $moduleName
		 * @param string $fieldName
		 */
		public function deleteReferencesByFieldName ($moduleName, $fieldName) {
			if ((empty ($moduleName)) || (empty ($fieldName))) {
				return;
			}

			$this->adb->pquery (
				'DELETE
					fmrf
				FROM
					vtiger_fieldmodulerel_filters fmrf
					INNER JOIN vtiger_fieldmodulerel fmr ON fmr.fieldpk=fmrf.referenceid
					INNER JOIN vtiger_field f ON f.fieldid=fmr.fieldid AND f.fieldname=?
					INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?',
				array ($fieldName, $moduleName)
			);
			$this->adb->pquery (
				'DELETE
					fmrr
				FROM
					vtiger_fieldmodulerel_relationships fmrr
					INNER JOIN vtiger_fieldmodulerel fmr ON fmr.fieldpk=fmrr.referenceid
					INNER JOIN vtiger_field f ON f.fieldid=fmr.fieldid AND f.fieldname=?
					INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?',
				array ($fieldName, $moduleName)
			);
			$this->adb->pquery (
				'DELETE
					fmr
				FROM
					vtiger_fieldmodulerel fmr
					INNER JOIN vtiger_field f ON f.fieldid=fmr.fieldid AND f.fieldname=?
					INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?',
				array ($fieldName, $moduleName)
			);
		}

		/**
		 * @param string $moduleName
		 * @param string $fieldName
		 * @param string $referencedModuleName
		 *
		 * @return FieldModuleReference|null
		 */
		public function fetchReference ($moduleName, $fieldName, $referencedModuleName) {
			if ((empty ($moduleName)) || (empty ($fieldName)) || (empty ($referencedModuleName))) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT
					fmr.*
				FROM
					vtiger_fieldmodulerel fmr
					INNER JOIN vtiger_field f ON f.fieldid=fmr.fieldid AND f.fieldname=?
					INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?
				WHERE
					fmr.relmodule=?',
				array ($fieldName, $moduleName, $referencedModuleName)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row       = $this->adb->fetchByAssoc ($result, -1, false);
				$reference = FieldModuleReference::getInstance ()
					->setFieldName ($fieldName)
					->setFilters ($this->fetchFilters ($row ['fieldpk']))
					->setLocked ($row ['locked'] == 1)
					->setModuleName ($moduleName)
					->setReferencedModuleName ($row ['relmodule'])
					->setRelationships ($this->fetchRelationships ($row ['fieldpk']))
					->setSequence ($row ['sequence'])
					->setStatus ($row ['status']);
			} else {
				$reference = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $reference;
		}

		/**
		 * @param string $moduleName
		 * @param string $fieldName
		 * @param boolean $includeDeleted
		 *
		 * @return FieldModuleReference[]|null
		 */
		public function fetchReferences ($moduleName, $fieldName, $includeDeleted = false) {
			if ((empty ($moduleName)) || (empty ($fieldName))) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT
					fmr.*
				FROM
					vtiger_fieldmodulerel fmr
					INNER JOIN vtiger_field f ON f.fieldid=fmr.fieldid AND f.fieldname=?
					INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?',
				array ($fieldName, $moduleName)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$references = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$references [] = FieldModuleReference::getInstance ()
						->setFieldName ($fieldName)
						->setFilters ($this->fetchFilters ($row ['fieldpk']))
						->setLocked ($row ['locked'] == 1)
						->setModuleName ($moduleName)
						->setReferencedModuleName ($row ['relmodule'])
						->setRelationships ($this->fetchRelationships ($row ['fieldpk']))
						->setSequence ($row ['sequence'])
						->setStatus ($row ['status']);
				}
				if ($includeDeleted) {
					$deletedReferences = $this->fetchDeletedReferences ($moduleName);
				} else {
					$deletedReferences = array ();
				}
				$references = array_merge ($references, $deletedReferences);
			} else {
				$references = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $references;
		}

		/**
		 * @param FieldModuleReference $reference
		 * @param boolean $ignoreLock
		 *
		 * @return FieldModuleReference
		 * @throws FieldModuleReferenceException
		 */
		public function saveReference ($reference, $ignoreLock = true) {
			$this->validate ($reference);

			$fieldName  = $reference->getFieldName ();
			$moduleName = $reference->getModuleName ();

			if (empty ($fieldName)) {
				throw new FieldModuleReferenceException (FieldModuleReferenceException::ERROR_FIELD_MODULE_REFERENCE_EMPTY_FIELD_NAME);
			} else if (empty ($moduleName)) {
				throw new FieldModuleReferenceException (FieldModuleReferenceException::ERROR_FIELD_MODULE_REFERENCE_EMPTY_MODULE_NAME);
			}

			$fieldId  = $this->getFieldId ($reference->getModuleName (), $reference->getFieldName ());
			$sequence = $reference->getSequence ();

			$result = $this->adb->pquery (
				'SELECT fmr.* FROM vtiger_fieldmodulerel fmr WHERE fmr.fieldid=? AND fmr.module=? AND fmr.relmodule=?',
				array ($fieldId, $reference->getModuleName (), $reference->getReferencedModuleName ())
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row      = $this->adb->fetchByAssoc ($result, -1, false);
				$isLocked = ($row ['locked'] == 1);
			} else {
				$row      = null;
				$isLocked = false;
			}
			if (empty ($row)) {
				if (!empty ($sequence)) {
					$sequence = intval ($sequence);
				} else {
					$sequence = $this->getSequenceNumber ($reference->getModuleName (), $reference->getFieldName ());
				}
				$reference->setSequence ($sequence);
				$this->adb->pquery (
					'INSERT INTO vtiger_fieldmodulerel (fieldid, module, relmodule, status, sequence, locked) VALUES (?, ?, ?, ?, ?, ?)',
					array ($fieldId, $reference->getModuleName (), $reference->getReferencedModuleName (), $reference->getStatus (), $reference->getSequence (), $reference->isLocked ())
				);
				$referenceId = $this->adb->getLastInsertID ();
			} else if (($ignoreLock) || (!$isLocked)) {
				if (!empty ($sequence)) {
					$sequence = intval ($sequence);
				} else if (!empty ($row ['sequence'])) {
					$sequence = intval ($row ['sequence']);
				} else {
					$sequence = $this->getSequenceNumber ($reference->getModuleName (), $reference->getFieldName ());
				}
				$reference->setSequence ($sequence);
				$this->adb->pquery (
					'UPDATE vtiger_fieldmodulerel SET status=?, sequence=?, locked=? WHERE fieldid=? AND module=? AND relmodule=?',
					array ($reference->getStatus (), $reference->getSequence (), $reference->isLocked (), $fieldId, $reference->getModuleName (), $reference->getReferencedModuleName ())
				);
				$referenceId = intval ($row ['fieldpk']);
			} else {
				$referenceId = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (!empty ($referenceId)) {
				$this->saveFilters ($referenceId, $reference);
				$this->saveRelationships ($referenceId, $reference);
			}
			return $reference;
		}

		/**
		 * @param FieldModuleReference[] $references
		 * @param boolean $ignoreLock
		 *
		 * @throws FieldModuleReferenceException
		 */
		public function saveReferences ($references, $ignoreLock = true) {
			if ((empty ($references)) || (!is_array ($references))) {
				throw new FieldModuleReferenceException (FieldModuleReferenceException::ERROR_FIELD_MODULE_REFERENCE_EMPTY_REFERENCE);
			}

			$this->adb->startTransaction ();
			foreach ($references as $reference) {
				$this->saveReference ($reference, $ignoreLock);
			}
			$this->adb->completeTransaction ();
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return FieldModuleReferenceManager
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
