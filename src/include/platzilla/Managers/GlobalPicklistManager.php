<?php
	require_once ('include/platzilla/Objects/GlobalPicklist.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	class GlobalPicklistManager {
		/** @var GlobalPicklistManager[]|null */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		/**
		 * @param GlobalPicklist $picklist
		 *
		 * @throws PicklistException
		 */
		private function validate ($picklist) {
			$picklist->validate ();

			$result = $this->adb->pquery ('SELECT * FROM vtiger_globalpicklists WHERE picklistname=?', array ($picklist->getName ()));
			if ($this->adb->num_rows ($result) > 0) {
				$row      = $this->adb->fetchByAssoc ($result, -1, false);
				$picklistId = $picklist->getId ();
				if ((empty ($picklistId)) || ($row ['picklistid'] != $picklistId)) {
					$e = new PicklistException (PicklistException::ERROR_PICKLIST_DUPLICATE_NAME);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($e)) {
				throw $e;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_globalpicklists WHERE picklistlabel=?', array ($picklist->getLabel ()));
			if ($this->adb->num_rows ($result) > 0) {
				$row        = $this->adb->fetchByAssoc ($result, -1, false);
				$picklistId = $picklist->getId ();
				if ((empty ($picklistId)) || ($row ['picklistid'] != $picklistId)) {
					$e = new PicklistException (PicklistException::ERROR_PICKLIST_DUPLICATE_LABEL);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($e)) {
				throw $e;
			}
		}

		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * @param string $picklistName
		 *
		 * @return GlobalPicklistValue[]|null
		 */
		private function fetchPicklistValues ($picklistName) {
			if (empty ($picklistName)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_globalpicklists_values WHERE picklistname=? ORDER BY picklistvalueid', array ($picklistName));
			if ($this->adb->num_rows ($result) > 0) {
				$values = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$values [] = GlobalPicklistValue::getInstance ()
						->setId (intval ($row ['picklistvalueid']))
						->setValue ($row ['picklistvalue']);
				}
			} else {
				$values = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $values;
		}

		/**
		 * @param GlobalPicklist $picklist
		 */
		private function savePicklistValues ($picklist) {
			$picklistName   = $picklist->getName ();
			$picklistValues = $picklist->getValues ();
			if (empty ($picklistValues)) {
				$this->adb->pquery ('DELETE FROM vtiger_globalpicklists_values WHERE picklistname=?', array ($picklistName));
				return;
			}

			$processedValueIds = array ();
			foreach ($picklistValues as $picklistValue) {
				$rawValue = $picklistValue->getValue ();
				$result   = $this->adb->pquery ('SELECT * FROM vtiger_globalpicklists_values WHERE picklistname=? AND picklistvalue=?', array ($picklistName, $rawValue));
				if ($this->adb->num_rows ($result) == 0) {
					$this->adb->pquery ('INSERT INTO vtiger_globalpicklists_values (picklistname, picklistvalue) VALUES (?, ?)', array ($picklistName, $picklistValue));
					$valueId = $this->adb->getLastInsertID ();
				} else {
					$row     = $this->adb->fetchByAssoc ($result, -1, false);
					$valueId = intval ($row ['picklistid']);
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
				$picklistValue->setId ($valueId);
				$processedValueIds [] = $valueId;
			}
			if (count ($processedValueIds) > 0) {
				$questionMarks = str_repeat ('?, ', (count ($processedValueIds) - 1)) . '?';
				$this->adb->pquery ("DELETE FROM vtiger_globalpicklists_values WHERE picklistname=? AND picklistvalueid NOT IN ({$questionMarks})", $processedValueIds);
			}
		}

		/**
		 * @param GlobalPicklist $picklist
		 */
		public function deletePicklist ($picklist) {
			if ((empty ($picklist)) || (!($picklist instanceof GlobalPicklist))) {
				return;
			}

			$name   = $picklist->getName ();
			$result = $this->adb->pquery (
				'SELECT
					*
				FROM
					vtiger_field f
				WHERE
					f.uitype=? AND
					f.fieldname=?',
				array (Field::UI_TYPE_GLOBAL_PICKLIST, $name)
			);
			if ($this->adb->num_rows ($result) == 0) {
				$this->adb->pquery ('DELETE FROM vtiger_globalpicklists_values WHERE picklistname=?', array ($name));
				$this->adb->pquery ('DELETE FROM vtiger_globalpicklists WHERE picklistname=?', array ($name));
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}

		/**
		 * @param string $picklistName
		 *
		 * @return GlobalPicklist|null
		 */
		public function fetchPicklistByName ($picklistName) {
			$result = $this->adb->pquery ('SELECT * FROM vtiger_globalpicklists WHERE picklistname=?', array ($picklistName));
			if ($this->adb->num_rows ($result) > 0) {
				$row      = $this->adb->fetchByAssoc ($result, -1, false);
				$picklist = GlobalPicklist::getInstance ()
					->setId (intval ($row ['picklistid']))
					->setLabel ($row ['picklistlabel'])
					->setMultiple ($row ['ismultiple'] == 1)
					->setName ($row ['picklistname'])
					->setValues ($this->fetchPicklistValues ($row ['picklistname']));
			} else {
				$picklist = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $picklist;
		}

		/**
		 * @param string $picklistName
		 *
		 * @return string[]|null
		 */
		public function fetchPicklistRawValues ($picklistName) {
			$picklistValues = $this->fetchPicklistValues ($picklistName);
			if (empty ($picklistValues)) {
				return null;
			}

			$rawValues = array ();
			foreach ($picklistValues as $picklistValue) {
				$rawValues [] = $picklistValue->getValue ();
			}
			return $rawValues;
		}

		/**
		 * @return GlobalPicklist[]|null
		 */
		public function fetchPicklists () {
			$result = $this->adb->query ('SELECT * FROM vtiger_globalpicklists ORDER BY picklistlabel');
			if ($this->adb->num_rows ($result) > 0) {
				$picklists = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$picklists[] = GlobalPicklist::getInstance ()
						->setId (intval ($row ['picklistid']))
						->setLabel ($row ['picklistlabel'])
						->setMultiple ($row ['ismultiple'] == 1)
						->setName ($row ['picklistname'])
						->setValues ($this->fetchPicklistValues ($row ['picklistname']));
				}
			} else {
				$picklists = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $picklists;
		}

		/**
		 * @param GlobalPicklist $picklist
		 *
		 * @return GlobalPicklist
		 * @throws PicklistException
		 */
		public function savePicklist ($picklist) {
			if ((empty ($picklist)) || (!($picklist instanceof GlobalPicklist))) {
				return null;
			}
			$this->validate ($picklist);

			$this->adb->startTransaction ();
			$picklistName = $picklist->getName ();
			$result       = $this->adb->pquery ('SELECT * FROM vtiger_globalpicklists WHERE picklistname=?', array ($picklistName));
			if ($this->adb->num_rows ($result) == 0) {
				$this->adb->pquery ('INSERT INTO vtiger_globalpicklists (picklistname, picklistlabel, ismultiple) VALUES (?, ?, ?)', array ($picklistName, $picklist->getLabel (), $picklist->isMultiple ()));
				$picklistId = $this->adb->getLastInsertID ();
			} else {
				$this->adb->pquery ('UPDATE vtiger_globalpicklists SET picklistlabel=?, ismultiple=? WHERE picklistname=?', array ($picklist->getLabel (), $picklist->isMultiple (), $picklistName));
				$row        = $this->adb->fetchByAssoc ($result, -1, false);
				$picklistId = intval ($row ['picklistid']);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			$this->savePicklistValues ($picklist);
			$this->adb->completeTransaction ();
			$picklist->setId ($picklistId);
			return $picklist;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return GlobalPicklistManager
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
