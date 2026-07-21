<?php
	require_once ('include/platzilla/Objects/ModuleRelationship.php');
	require_once ('include/platzilla/Objects/ModuleRelationshipFields.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	class ModuleRelationshipManager {
		/** @var ModuleRelationshipManager[]|null */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * @param string $moduleName
		 *
		 * @return ModuleRelationship[]
		 */
		private function fetchDeletedRelationships ($moduleName) {
			if (empty ($moduleName)) {
				return array ();
			}

			$relationships = array ();
			$result        = $this->adb->pquery ('SELECT * FROM vtiger_deletedelements WHERE elementtype=? AND modulename=?', array ('modulerelationship', $moduleName));
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					/** @var ModuleRelationship $relationship */
					$relationship = unserialize ($row ['serializedobject']);
					$relationship->setDeleted (true);
					$relationships [] = $relationship;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $relationships;
		}

		/**
		 * @param string $moduleName
		 *
		 * @return integer
		 */
		private function getSequenceNumber ($moduleName) {
			$result = $this->adb->pquery ('SELECT MAX(sequence) AS maxsequence FROM vtiger_relatedlists WHERE tabid IN (SELECT tabid FROM vtiger_tab WHERE name=?)', array ($moduleName));
			if ($this->adb->num_rows ($result) > 0) {
				$row      = $this->adb->fetchByAssoc ($result, -1, false);
				$sequence = (intval ($row ['maxsequence']) + 1);
			} else {
				$sequence = 1;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $sequence;
		}

		/**
		 * @param ModuleRelationship $relationship
		 *
		 * @throws ModuleRelationshipException
		 */
		private function validate ($relationship) {
			if ((empty ($relationship)) || (!($relationship instanceof ModuleRelationship))) {
				throw new ModuleRelationshipException (ModuleRelationshipException::ERROR_MODULE_RELATIONSHIP_EMPTY);
			}

			$relationship->validate ();

			$result = $this->adb->pquery ('SELECT * FROM vtiger_tab WHERE name=?', array ($relationship->getModuleName ()));
			if ($this->adb->num_rows ($result) == 0) {
				$e = new ModuleRelationshipException (ModuleRelationshipException::ERROR_MODULE_RELATIONSHIP_INVALID_MODULE_NAME);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($e)) {
				throw $e;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_tab WHERE name=?', array ($relationship->getRelatedModuleName ()));
			if ($this->adb->num_rows ($result) == 0) {
				$e = new ModuleRelationshipException (ModuleRelationshipException::ERROR_MODULE_RELATIONSHIP_INVALID_RELATED_MODULE_NAME);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($e)) {
				throw $e;
			}
		}

		/**
		 * @param ModuleRelationship $relationship
		 */
		public function deleteRelationship ($relationship) {
			if ((empty ($relationship)) || (!($relationship instanceof ModuleRelationship))) {
				return;
			}

			$moduleName        = $relationship->getModuleName ();
			$relatedModuleName = $relationship->getRelatedModuleName ();
			$function          = $relationship->getFunction ();
			if ((empty ($moduleName)) || (empty ($relatedModuleName)) || (empty ($function))) {
				return;
			}

			$identifier = "{$moduleName}-{$relatedModuleName}-{$function}";
			if ((!empty ($moduleName)) && (!empty ($identifier))) {
				$this->adb->pquery ('DELETE FROM vtiger_deletedelements WHERE elementtype=? AND modulename=? AND identifier=?', array ('modulerelationship', $moduleName, $identifier));
				$this->adb->pquery ('INSERT INTO vtiger_deletedelements (elementtype, modulename, identifier, deletedon, serializedobject) VALUES (?, ?, ?, ?, ?)', array ('modulerelationship', $moduleName, $identifier, date ('Y-m-d h:i:s'), serialize ($relationship)));
			}

			$this->adb->pquery (
				'DELETE
					rl
				FROM
					vtiger_relatedlists rl
					INNER JOIN vtiger_tab tm ON tm.tabid=rl.tabid AND tm.name=?
					INNER JOIN vtiger_tab trm ON trm.tabid=rl.related_tabid AND trm.name=?
				WHERE
					rl.name=?',
				array ($moduleName, $relatedModuleName, $function)
			);
		}

		public function duplicateRelationships ($oldModuleName, $newModuleName) {
			if ((empty ($oldModuleName)) || (empty ($newModuleName))) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT
					rl.*,
					tm.name AS modulename,
					trm.name AS relatedmodulename
				FROM
					vtiger_relatedlists rl
					INNER JOIN vtiger_tab trm ON trm.tabid=rl.related_tabid
					INNER JOIN vtiger_tab tm ON tm.tabid=rl.tabid
				WHERE
					tm.name=? OR trm.name=?',
				array ($oldModuleName, $oldModuleName)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$relationships = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$relationships [] = ModuleRelationship::getInstance ()
						->setActions (explode (',', $row ['actions']))
						->setFunction ($row ['name'])
						->setLabel ($row ['label'])
						->setModuleName ($row ['modulename'] != $oldModuleName ? $row ['modulename'] : $newModuleName)
						->setPresence (intval ($row ['presence']))
						->setRelatedModuleName ($row ['relatedmodulename'] != $oldModuleName ? $row ['relatedmodulename'] : $newModuleName)
						->setSequence (intval ($row ['sequence']));
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
		 * @param boolean $ignoreLock
		 */
		public function deleteRelationships ($moduleName = null, $ignoreLock = true) {
			if (empty ($moduleName)) {
				$this->adb->query ('DELETE rl FROM vtiger_relatedlists rl');
			} else {
				if (!$ignoreLock) {
					$whereClause       = 'AND rl.locked=0';
					$whereFieldsClause = 'AND locked=0';
				} else {
					$whereClause = '';
				}
				$this->adb->pquery ("DELETE rl FROM vtiger_relatedlists rl INNER JOIN vtiger_tab t ON t.tabid=rl.tabid AND t.name=? {$whereClause}", array ($moduleName));
				$this->adb->pquery ("DELETE rl FROM vtiger_relatedlists rl INNER JOIN vtiger_tab t ON t.tabid=rl.related_tabid AND t.name=? {$whereClause}", array ($moduleName));
				$this->adb->pquery ("DELETE  FROM vtiger_relatedlists_field  WHERE tabname=? {$whereFieldsClause}", array ($moduleName));
			}
		}
		
		/**
		 * @param integer $relationId
		 *
		 * @return ModuleRelationshipFields|null
		 * @throws Exception
		 */
		public function fetchRelationFieldById ($relationId) {
			if (empty ($relationId)) {
				return null;
			}
			
			$result = $this->adb->pquery ('SELECT * FROM vtiger_relatedlists_field WHERE relation_id=?', array ($relationId));
			if ($this->adb->num_rows ($result) > 0) {
				$row          = $this->adb->fetchByAssoc ($result, -1, false);
				$relationshipFields = ModuleRelationshipFields::getInstance ()
					->setFieldImport (json_decode ($row['field_import'], true))
					->setFieldList (json_decode ($row['fields_list'], true))
					->setModuleName ($row['tabname'])
					->setRelationId ($row ['relation_id'])
					->setLocked (($row ['locked']) ? true : false);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($relationshipFields)) ? $relationshipFields : null;
		}

		/**
		 * @param string $moduleName
		 * @param string $relatedModuleName
		 * @param string $function
		 *
		 * @return ModuleRelationship|null
		 * @throws Exception
		 */
		public function fetchRelationship ($moduleName, $relatedModuleName, $function) {
			if ((empty ($moduleName)) || (empty ($relatedModuleName)) || (empty ($function))) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT
					rl.*
				FROM
					vtiger_relatedlists rl
					INNER JOIN vtiger_tab tm ON tm.tabid=rl.tabid AND tm.name=?
					INNER JOIN vtiger_tab trm ON trm.tabid=rl.related_tabid AND trm.name=?
				WHERE
					rl.name=?',
				array ($moduleName, $relatedModuleName, $function)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row          = $this->adb->fetchByAssoc ($result, -1, false);
				$relationship = ModuleRelationship::getInstance ()
					->setActions (explode (',', $row ['actions']))
					->setFunction ($function)
					->setLabel ($row ['label'])
					->setLocked ($row ['locked'] == 1)
					->setModuleName ($moduleName)
					->setPresence (intval ($row ['presence']))
					->setRelatedModuleName ($relatedModuleName)
					->setRelatedFields ($this->fetchRelationFieldById ($row ['relation_id']))
					->setRelationId ($row ['relation_id'])
					->setSequence (intval ($row ['sequence']));
			} else {
				$relationship = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $relationship;
		}

		/**
		 * @param string $moduleName
		 * @param boolean $includeDeleted
		 *
		 * @return ModuleRelationship[]|null
		 * @throws Exception
		 */
		public function fetchRelationships ($moduleName = null, $includeDeleted = false) {
			if (!empty ($moduleName)) {
				$fromClause = 'AND tm.name=?';
				$arguments  = array ($moduleName);
			} else {
				$fromClause = '';
				$arguments  = array ();
			}

			$result = $this->adb->pquery (
				"SELECT
					rl.*,
					tm.name AS modulename,
					trm.name AS relatedmodulename,
					trm.tablabel
				FROM
					vtiger_relatedlists rl
					INNER JOIN vtiger_tab trm ON trm.tabid=rl.related_tabid
					INNER JOIN vtiger_tab tm ON tm.tabid=rl.tabid {$fromClause}
				ORDER BY
					rl.sequence",
				$arguments
			);
			if ($this->adb->num_rows ($result) > 0) {
				$relationships = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$relationships [] = ModuleRelationship::getInstance ()
						->setActions (explode (',', $row ['actions']))
						->setFunction ($row ['name'])
						->setLabel ($row ['label'])
						->setLocked ($row ['locked'] == 1)
						->setModuleName ($row ['modulename'])
						->setPresence (intval ($row ['presence']))
						->setRelatedFields ($this->fetchRelationFieldById ($row ['relation_id']))
						->setRelationId ($row ['relation_id'])
						->setRelatedModuleName ($row ['relatedmodulename'])
						->setRelatedModuleLabel ($row ['tablabel'])
						->setSequence (intval ($row ['sequence']));
				}

				if ($includeDeleted) {
					$deletedRelationships = $this->fetchDeletedRelationships ($moduleName);
				} else {
					$deletedRelationships = array ();
				}

				$relationships = array_merge ($relationships, $deletedRelationships);
			} else {
				$relationships = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $relationships;
		}
		
		/**
		 * @param ModuleRelationship $relationship
		 * @param boolean $ignoreLock
		 *
		 * @return ModuleRelationship
		 * @throws ModuleRelationshipException
		 */
		public function saveRelationship ($relationship, $ignoreLock = true) {
			$this->validate ($relationship);

			if ($relationship->isDeleted ()) {
				return $relationship;
			}

			$sequence = $relationship->getSequence ();
			$sequence = !empty ($sequence) ? $sequence : $this->getSequenceNumber ($relationship->getModuleName ());
			$this->adb->startTransaction ();
			$result = $this->adb->pquery (
				'SELECT
					rl.*
				FROM
					vtiger_relatedlists rl
					INNER JOIN vtiger_tab tm ON tm.tabid=rl.tabid AND tm.name=?
					INNER JOIN vtiger_tab trm ON trm.tabid=rl.related_tabid AND trm.name=?
				WHERE
					rl.name=?',
				array ($relationship->getModuleName (), $relationship->getRelatedModuleName (), $relationship->getFunction ())
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row            = $this->adb->fetchByAssoc ($result, -1, false);
				$isLocked       = ($row ['locked'] == 1);
				$relationshipId = $row ['relation_id'];
			} else {
				$isLocked       = false;
				$relationshipId = null;
			}
			if (empty ($relationshipId)) {
				$relationshipId = $this->adb->getUniqueID ('vtiger_relatedlists');
				$this->adb->pquery (
					'INSERT INTO vtiger_relatedlists (relation_id, tabid, related_tabid, name, sequence, label, presence, actions, relfield, locked) VALUES
					(?, (SELECT tabid FROM vtiger_tab WHERE name=?), (SELECT tabid FROM vtiger_tab WHERE name=?), ?, ?, ?, ?, ?, ?, ?)',
					array ($relationshipId, $relationship->getModuleName (), $relationship->getRelatedModuleName (), $relationship->getFunction (), $sequence, $relationship->getLabel (), $relationship->getPresence (), join (',', $relationship->getActions ()), null, $relationship->isLocked ())
				);
				$relationship->setRelationId ($relationshipId);
			} else if (($ignoreLock) || (!$isLocked)) {
				$row            = $this->adb->fetchByAssoc ($result, -1, false);
				$relationshipId = intval ($row ['relation_id']);
				$this->adb->pquery (
					'UPDATE vtiger_relatedlists SET sequence=?, label=?, presence=?, actions=?, relfield=?, locked=? WHERE relation_id=?',
					array ($sequence, $relationship->getLabel (), $relationship->getPresence (), join (',', $relationship->getActions ()), null, $relationship->isLocked (), $relationshipId)
				);
			}
			$this->adb->completeTransaction ();
			if (!empty($relationship->getRelatedFields ())) {
				$relationship->getRelatedFields ()->setRelationId ($relationship->getRelationId ());
				$this->saveRelationshipFields ($relationship);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $relationship;
		}

		/**
		 * @param string $moduleName
		 * @param ModuleRelationship[] $relationships
		 * @param boolean $ignoreLock
		 *
		 * @throws Exception
		 */
		public function saveRelationships ($moduleName, $relationships, $ignoreLock = true) {
			if (empty ($moduleName)) {
				return;
			} else if (empty ($relationships)) {
				$this->deleteRelationships ($moduleName, $ignoreLock);
				return;
			}

			$processedRelationshipIds = array ();
			foreach ($relationships as $relationship) {
				if ($relationship->isDeleted ()) {
					continue;
				}

				$relationship->setModuleName ($moduleName);
				$this->validate ($relationship);

				$sequence = !empty ($relationship->getSequence ()) ? $relationship->getSequence () : $this->getSequenceNumber ($relationship->getModuleName ());
				$result   = $this->adb->pquery (
					'SELECT
						rl.*
					FROM
						vtiger_relatedlists rl
						INNER JOIN vtiger_tab tm ON tm.tabid=rl.tabid AND tm.name=?
						INNER JOIN vtiger_tab trm ON trm.tabid=rl.related_tabid AND trm.name=?
					WHERE
						rl.name=?',
					array ($relationship->getModuleName (), $relationship->getRelatedModuleName (), $relationship->getFunction ())
				);
				if ($this->adb->num_rows ($result) > 0) {
					$row            = $this->adb->fetchByAssoc ($result, -1, false);
					$isLocked       = ($row ['locked'] == 1);
					$relationshipId = intval ($row ['relation_id']);
				} else {
					$isLocked       = false;
					$relationshipId = null;
				}
				DatabaseUtils::closeResult ($result);
				$result = null;

				if (empty ($relationshipId)) {
					$relationshipId = $this->adb->getUniqueID ('vtiger_relatedlists');
					$this->adb->pquery (
						'INSERT INTO vtiger_relatedlists (relation_id, tabid, related_tabid, name, sequence, label, presence, actions, relfield, locked) VALUES
						(?, (SELECT tabid FROM vtiger_tab WHERE name=?), (SELECT tabid FROM vtiger_tab WHERE name=?), ?, ?, ?, ?, ?, ?, ?)',
						array ($relationshipId, $relationship->getModuleName (), $relationship->getRelatedModuleName (), $relationship->getFunction (), $sequence, $relationship->getLabel (), $relationship->getPresence (), join (',', $relationship->getActions ()), null, $relationship->isLocked ())
					);
				} else if (($ignoreLock) || (!$isLocked)) {
					$this->adb->pquery (
						'UPDATE vtiger_relatedlists SET sequence=?, label=?, presence=?, actions=?, relfield=?, locked=? WHERE relation_id=?',
						array ($sequence, $relationship->getLabel (), $relationship->getPresence (), join (',', $relationship->getActions ()), null, $relationship->isLocked (), $relationshipId)
					);
				}
				$processedRelationshipIds [] = $relationshipId;
				if (!empty ($relationship->getRelatedFields ())) {
					$relationship->setRelationId ($relationshipId);
					$this->saveRelationshipFields ($relationship);
				}
			}

			$questionMarks = str_repeat ('?, ', (count ($processedRelationshipIds) - 1)) . '?';
			$this->adb->pquery (
				"DELETE
					rl
				FROM
					vtiger_relatedlists rl
					INNER JOIN vtiger_tab t ON t.tabid=rl.tabid AND t.name=?
				WHERE
					rl.relation_id NOT IN ({$questionMarks})",
				array_merge (array ($moduleName), $processedRelationshipIds)
			);
		}
		
		/**
		 * @param ModuleRelationship $relationship
		 *
		 * @throws Exception
		 */
		public function saveRelationshipFields ($relationship) {
			if (empty ($relationship) || empty ($relationship->getRelatedFields ())) {
				return;
			}
			$relationshipField = $relationship->getRelatedFields ();
			$oldRelationField  = $this->fetchRelationFieldById ($relationship->getRelationId ());
			if (!empty ($oldRelationField) && $oldRelationField->isLocked ()) {
				return;
			} else if(!empty ($oldRelationField)) {
				$this->adb->pquery ('DELETE FROM vtiger_relatedlists_field WHERE relation_id=?', array ($relationship->getRelationId ()));
			}
			
			$locked      = ($relationship->isLocked ()) ? 1 : 0;
			$fieldList   = (!empty($relationshipField->getFieldList ())) ? json_encode ($relationshipField->getFieldList ()) : null;
			$fieldImport = (!empty($relationshipField->getFieldImport ())) ? json_encode ($relationshipField->getFieldImport ()) : null;
			$this->adb->pquery (
				'INSERT INTO vtiger_relatedlists_field (relation_id, fields_list, field_import, tabname, locked) VALUES (?, ?, ?, ?, ?)',
				array ($relationship->getRelationId (), $fieldList, $fieldImport, $relationshipField->getModuleName (), $locked)
			);
		}
		
		/**
		 * @param ModuleRelationship $relationship
		 *
		 * @throws ModuleRelationshipException
		 */
		public function validateRelationship ($relationship) {
			$this->validate ($relationship);
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return ModuleRelationshipManager
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
