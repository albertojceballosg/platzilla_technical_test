<?php
	require_once ('include/platzilla/Objects/DataSharingRule.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	class DataSharingRuleManager {
		/** @var DataSharingRuleManager[] */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		/**
		 * @param $moduleName
		 *
		 * @return DataSharingRule[]
		 */
		private function fetchDeletedRules ($moduleName) {
			if (empty ($moduleName)) {
				return array ();
			}

			$rules  = array ();
			$result = $this->adb->pquery ('SELECT * FROM vtiger_deletedelements WHERE elementtype=? AND modulename=?', array ('datasharingrule', $moduleName));
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					/** @var DataSharingRule $rule */
					$rule = unserialize ($row ['serializedobject']);
					$rule->setDeleted (true);
					$rules [] = $rule;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $rules;
		}

		/**
		 * @param $ruleId
		 *
		 * @return DataSharingRuleDetail[]|null
		 */
		private function fetchRuleDetails ($ruleId) {
			if (empty ($ruleId)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_instancesdatasharing_rulesdetails WHERE ruleid=?', array ($ruleId));
			if ($this->adb->num_rows ($result) > 0) {
				$details = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$details [] = DataSharingRuleDetail::getInstance ()
						->setId (intval ($row ['ruledetailid']))
						->setActionType ($row ['actiontype'])
						->setParameterFormula ($row ['parameterformula'])
						->setParameterType ($row ['parametertype'])
						->setRuleId (intval ($row ['ruleid']))
						->setSourceModuleName ($row ['sourcemodulename'])
						->setTargetFieldName ($row ['targetfieldname'])
						->setTargetModuleName ($row ['targetmodulename']);
				}
			} else {
				$details = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $details;
		}

		/**
		 * @param DataSharingRule $rule
		 */
		private function saveRuleDetails ($rule) {
			$details = $rule->getDetails ();
			if (empty ($details)) {
				return;
			}

			$ruleId             = $rule->getId ();
			$processedDetailIds = array ();
			foreach ($details as $detail) {
				$detailId = $detail->getId ();
				if (empty ($detailId)) {
					$this->adb->pquery (
						'INSERT INTO vtiger_instancesdatasharing_rulesdetails (ruleid, sourcemodulename, targetmodulename, targetfieldname, actiontype, parametertype, parameterformula) VALUES (?, ?, ?, ?, ?, ?, ?)',
						array ($ruleId, $detail->getSourceModuleName (), $detail->getTargetModuleName (), $detail->getTargetFieldName (), $detail->getActionType (), $detail->getParameterType (), $detail->getParameterFormula ())
					);
					$detailId = $this->adb->getLastInsertID ();
				} else {
					$this->adb->pquery (
						'UPDATE vtiger_instancesdatasharing_rulesdetails SET ruleid=?, sourcemodulename=?, targetmodulename=?, targetfieldname=?, actiontype=?, parametertype=?, parameterformula=? WHERE ruledetailid=?',
						array ($ruleId, $detail->getSourceModuleName (), $detail->getTargetModuleName (), $detail->getTargetFieldName (), $detail->getActionType (), $detail->getParameterType (), $detail->getParameterFormula (), $detailId)
					);
				}
				$detail->setId ($detailId)
					->setRuleId ($ruleId);
				$processedDetailIds [] = $detailId;
			}

			$questionMarks = str_repeat ('?, ', (count ($processedDetailIds) - 1)) . '?';
			$this->adb->pquery ("DELETE FROM vtiger_instancesdatasharing_rulesdetails WHERE ruleid=? AND ruledetailid NOT IN ({$questionMarks})", array_merge (array ($ruleId), $processedDetailIds));
		}

		/**
		 * @param DataSharingRule $rule
		 *
		 * @throws DataSharingRuleDetailException
		 * @throws DataSharingRuleException
		 */
		private function validate ($rule) {
			if ((empty ($rule)) || (!($rule instanceof DataSharingRule))) {
				throw new DataSharingRuleException (DataSharingRuleException::ERROR_DATA_SHARING_RULE_EMPTY);
			}

			$rule->validate ();

			$moduleName = $rule->getModuleName ();
			$result     = $this->adb->pquery ('SELECT * FROM vtiger_tab WHERE name=?', array ($moduleName));
			$totalRows  = $this->adb->num_rows ($result);
			DatabaseUtils::closeResult ($result);
			$result = null;
			if ($totalRows == 0) {
				throw new DataSharingRuleException (DataSharingRuleException::ERROR_DATA_SHARING_RULE_INVALID_MODULE_NAME);
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_instancesdatasharing_rules WHERE modulename=? AND rulename=?', array ($moduleName, $rule->getName ()));
			if ($this->adb->num_rows ($result) > 0) {
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				DatabaseUtils::closeResult ($result);
				$result = null;
				$ruleId = $rule->getId ();
				if ((empty ($ruleId)) || ($row ['ruleid'] != $ruleId)) {
					throw new DataSharingRuleException (DataSharingRuleException::ERROR_DATA_SHARING_RULE_DUPLICATE_NAME);
				}
				$this->validateDetails ($rule);
			} else {
				DatabaseUtils::closeResult ($result);
				$result = null;
			}
		}

		/**
		 * @param DataSharingRule $rule
		 *
		 * @throws DataSharingRuleDetailException
		 */
		private function validateDetails ($rule) {
			$details = $rule->getDetails ();
			if (empty ($details)) {
				return;
			}

			foreach ($details as $detail) {
				$result = $this->adb->pquery ('SELECT * FROM vtiger_tab WHERE name=?', array ($detail->getSourceModuleName ()));
				if ($this->adb->num_rows ($result) == 0) {
					$e = new DataSharingRuleDetailException (DataSharingRuleDetailException::ERROR_DATA_SHARING_RULE_DETAIL_INVALID_SOURCE_MODULE_NAME);
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
				if (isset ($e)) {
					throw $e;
				}

				$result = $this->adb->pquery ('SELECT * FROM vtiger_tab WHERE name=?', array ($detail->getTargetModuleName ()));
				if ($this->adb->num_rows ($result) == 0) {
					$e = new DataSharingRuleDetailException (DataSharingRuleDetailException::ERROR_DATA_SHARING_RULE_DETAIL_INVALID_TARGET_MODULE_NAME);
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
				if (isset ($e)) {
					throw $e;
				}

				$result = $this->adb->pquery (
					'SELECT f.* FROM vtiger_field f INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=? WHERE f.fieldname=?',
					array ($detail->getTargetModuleName (), $detail->getTargetFieldName ())
				);
				if ($this->adb->num_rows ($result) == 0) {
					$e = new DataSharingRuleDetailException (DataSharingRuleDetailException::ERROR_DATA_SHARING_RULE_DETAIL_INVALID_TARGET_FIELD_NAME);
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
				if (isset ($e)) {
					throw $e;
				}
			}
		}

		/**
		 * DataSharingRuleManager constructor.
		 *
		 * @param PearDatabase $adb
		 */
		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * @param DataSharingRule $rule
		 */
		public function deleteRule ($rule) {
			if ((empty ($rule)) || (!($rule instanceof DataSharingRule))) {
				return;
			}

			$ruleId = $rule->getId ();
			if (empty ($ruleId)) {
				return;
			}

			$moduleName = $rule->getModuleName ();
			$identifier = $rule->getName ();
			$this->adb->startTransaction ();
			if ((!empty ($moduleName)) && (!empty ($identifier))) {
				$this->adb->pquery ('DELETE FROM vtiger_deletedelements WHERE elementtype=? AND modulename=? AND identifier=?', array ('datasharingrule', $moduleName, $identifier));
				$this->adb->pquery ('INSERT INTO vtiger_deletedelements (elementtype, modulename, identifier, deletedon, serializedobject) VALUES (?, ?, ?, ?, ?)', array ('datasharingrule', $moduleName, $identifier, date ('Y-m-d h:i:s'), serialize ($rule)));
			}
			$this->adb->pquery ('DELETE FROM vtiger_instancesdatasharing_rulesdetails WHERE ruleid=?', array ($ruleId));
			$this->adb->pquery ('DELETE FROM vtiger_instancesdatasharing_rules WHERE ruleid=?', array ($ruleId));
			$this->adb->completeTransaction ();
		}

		/**
		 * @param string $moduleName
		 * @param boolean $ignoreLock
		 */
		public function deleteRules ($moduleName, $ignoreLock = true) {
			if (empty ($moduleName)) {
				return;
			}

			if (!$ignoreLock) {
				$whereClause = 'AND locked=0';
			} else {
				$whereClause = '';
			}
			$this->adb->startTransaction ();
			$this->adb->pquery ("DELETE FROM vtiger_instancesdatasharing_rulesdetails WHERE ruleid IN (SELECT ruleid FROM vtiger_instancesdatasharing_rules WHERE modulename=? {$whereClause})", array ($moduleName));
			$this->adb->pquery ("DELETE FROM vtiger_instancesdatasharing_rules WHERE modulename=? {$whereClause}", array ($moduleName));
			$this->adb->completeTransaction ();
		}

		/**
		 * @param integer $ruleId
		 *
		 * @return DataSharingRule|null
		 */
		public function fetchRuleById ($ruleId) {
			if (empty ($ruleId)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_instancesdatasharing_rules WHERE ruleid=?', array ($ruleId));
			if ($this->adb->num_rows ($result) > 0) {
				$row  = $this->adb->fetchByAssoc ($result, -1, false);
				$rule = DataSharingRule::getInstance ()
					->setId (intval ($row ['ruleid']))
					->setDetails ($this->fetchRuleDetails ($row ['ruleid']))
					->setLocked ($row ['locked'] == 1)
					->setModuleName ($row ['modulename'])
					->setName ($row ['rulename'])
					->setStatus ($row ['rulestatus']);
			} else {
				$rule = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $rule;
		}

		/**
		 * @param string $moduleName
		 * @param string $ruleName
		 *
		 * @return DataSharingRule|null
		 */
		public function fetchRuleByName ($moduleName, $ruleName) {
			if ((empty ($moduleName)) || (empty ($ruleName))) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_instancesdatasharing_rules WHERE modulename=? AND rulename=?', array ($moduleName, $ruleName));
			if ($this->adb->num_rows ($result) > 0) {
				$row  = $this->adb->fetchByAssoc ($result, -1, false);
				$rule = DataSharingRule::getInstance ()
					->setId (intval ($row ['ruleid']))
					->setDetails ($this->fetchRuleDetails ($row ['ruleid']))
					->setLocked ($row ['locked'] == 1)
					->setModuleName ($row ['modulename'])
					->setName ($row ['rulename'])
					->setStatus ($row ['rulestatus']);
			} else {
				$rule = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $rule;
		}

		/**
		 * @param string|null $moduleName
		 * @param boolean $includeDeleted
		 *
		 * @return DataSharingRule[]|null
		 */
		public function fetchRules ($moduleName, $includeDeleted = false) {
			if (empty ($moduleName)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_instancesdatasharing_rules WHERE modulename=? ORDER BY rulename', array ($moduleName));
			if ($this->adb->num_rows ($result) > 0) {
				$rules = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$rules [] = DataSharingRule::getInstance ()
						->setId (intval ($row ['ruleid']))
						->setDetails ($this->fetchRuleDetails ($row ['ruleid']))
						->setLocked ($row ['locked'] == 1)
						->setModuleName ($row ['modulename'])
						->setName ($row ['rulename'])
						->setStatus ($row ['rulestatus']);
				}
				if ($includeDeleted) {
					$deletedRules = $this->fetchDeletedRules ($moduleName);
				} else {
					$deletedRules = array ();
				}
				$rules = array_merge ($rules, $deletedRules);
			} else {
				$rules = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $rules;
		}

		/**
		 * @param DataSharingRule $rule
		 * @param boolean $ignoreLock
		 *
		 * @return DataSharingRule
		 * @throws DataSharingRuleException
		 * @throws DataSharingRuleDetailException
		 */
		public function saveRule ($rule, $ignoreLock = true) {
			$this->validate ($rule);

			$isDeleted = $rule->isDeleted ();
			if ($isDeleted) {
				return $rule;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_instancesdatasharing_rules WHERE modulename=? AND rulename=?', array ($rule->getModuleName (), $rule->getName ()));
			if (($result) && ($this->adb->num_rows ($result) > 0)) {
				$row      = $this->adb->fetchByAssoc ($result, -1, false);
				$ruleId   = intval ($row ['ruleid']);
				$isLocked = ($row ['locked'] == 1);
			} else {
				$ruleId  = null;
				$isLocked = false;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			$this->adb->startTransaction ();
			if (empty ($ruleId)) {
				$this->adb->pquery (
					'INSERT INTO vtiger_instancesdatasharing_rules (rulename, modulename, rulestatus, locked) VALUES (?, ?, ?, ?)',
					array ($rule->getName (), $rule->getModuleName (), $rule->getStatus (), $rule->isLocked ())
				);
				$rule->setId ($this->adb->getLastInsertID ());
				$this->saveRuleDetails ($rule);
			} else if (($ignoreLock) || (!$isLocked)) {
				$this->adb->pquery (
					'UPDATE vtiger_instancesdatasharing_rules SET rulename=?, modulename=?, rulestatus=?, locked=? WHERE ruleid=?',
					array ($rule->getName (), $rule->getModuleName (), $rule->getStatus (), $rule->isLocked (), $ruleId)
				);
				$rule->setId ($ruleId);
				$this->saveRuleDetails ($rule);
			}
			$this->adb->completeTransaction ();
			return $rule;
		}

		/**
		 * @param string|null $keyword
		 * @param integer|null $page
		 * @param integer|null $recordsPerPage
		 *
		 * @return DataSharingRule[]
		 */
		public function searchRules ($keyword = null, $page = null, $recordsPerPage = null) {
			$whereClauses = array ();
			$arguments    = array ();
			if (!empty ($keyword)) {
				$whereClauses [] = 'rulename LIKE ?';
				$arguments []    = "%{$keyword}%";
				$arguments []    = "%{$keyword}%";
			}
			$whereClause = !empty ($whereClauses) ? 'WHERE ' . join (' AND ', $whereClauses) : '';

			if ((!empty ($recordsPerPage)) && (is_numeric ($recordsPerPage))) {
				$startRecord = (!empty ($page)) && ($page > 0) ? (($page - 1) * $recordsPerPage) : 0;
				$limit       = $recordsPerPage;
				$limitClause = "LIMIT {$startRecord}, {$limit}";
			} else {
				$startRecord = 0;
				$limitClause = '';
			}

			$result = $this->adb->pquery (
				"SELECT
					*
				FROM
					vtiger_instancesdatasharing_rules
					CROSS JOIN (SELECT COUNT(*) AS __total_records__ FROM vtiger_instancesdatasharing_rules {$whereClause}) AS total
				{$whereClause}
				ORDER BY
					rulename
				{$limitClause}",
				$arguments
			);
			if ($this->adb->num_rows ($result) > 0) {
				$startRecord++;
				$totalRecords = null;
				$records      = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$totalRecords = intval ($row ['__total_records__']);
					$records []   = DataSharingRule::getInstance ()
						->setId (intval ($row ['ruleid']))
						->setDetails ($this->fetchRuleDetails ($row ['ruleid']))
						->setLocked ($row ['locked'] == 1)
						->setModuleName ($row ['modulename'])
						->setName ($row ['rulename'])
						->setStatus ($row ['rulestatus']);
				}
				$endRecord  = count ($records);
				$totalPages = ceil ($totalRecords / $recordsPerPage);
			} else {
				$totalRecords = 0;
				$records      = null;
				$endRecord    = 0;
				$totalPages   = 0;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			return array (
				'startRecord'  => $startRecord,
				'endRecord'    => $endRecord,
				'totalRecords' => $totalRecords,
				'page'         => empty ($page) ? 1 : intval ($page),
				'totalPages'   => $totalPages,
				'records'      => $records,
			);
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return DataSharingRuleManager
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
