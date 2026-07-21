<?php
	require_once ('include/platzilla/Objects/Pricebook.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/PlatformUtils.class.php');

	class PricebookManager {
		const RECORDS_PER_PAGE = 25;

		/** @var PricebookManager */
		private static $INSTANCE = null;

		/** @var PearDatabase */
		private $adb;

		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * @param string $variableValue
		 * @param string $comparator
		 * @param string $value
		 *
		 * @return boolean
		 */
		private function compare ($variableValue, $comparator, $value) {
			switch ($comparator) {
				case PricebookCondition::COMPARATOR_EQUALS:
					$result = ($value === null) ? (($variableValue === null) || ($variableValue === '')) : ($variableValue == $value);
					break;
				case PricebookCondition::COMPARATOR_GREATER:
					$result = ($variableValue > $value);
					break;
				case PricebookCondition::COMPARATOR_GREATER_OR_EQUALS:
					$result = ($variableValue >= $value);
					break;
				case PricebookCondition::COMPARATOR_LESS:
					$result = ($variableValue < $value);
					break;
				case PricebookCondition::COMPARATOR_LESS_OR_EQUALS:
					$result = ($variableValue <= $value);
					break;
				case PricebookCondition::COMPARATOR_NOT_EQUALS:
					$result = ($value === null) ? (($variableValue !== null) && ($variableValue !== '')) : ($variableValue != $value);
					break;
				case PricebookCondition::COMPARATOR_CONTAINS:
					$result = (preg_match ("/{$value}/", $variableValue) == 1);
					break;
				case PricebookCondition::COMPARATOR_DOES_NOT_CONTAIN:
					$result = (preg_match ("/{$value}/", $variableValue) == 0);
					break;
				default:
					$result = false;
					break;
			}
			return !!$result;
		}

		/**
		 * @param PricebookCondition $condition
		 * @param clientes $customer
		 * @param array $systemVariables
		 *
		 * @return boolean
		 */
		private function evaluateCondition ($condition, clientes $customer, array $systemVariables) {
			if (empty ($condition)) {
				return true;
			}

			$variableType = $condition->getVariableType ();
			$variableName = $condition->getVariableName ();
			$comparator   = $condition->getComparator ();
			$value        = $condition->getValue ();

			if (($variableType == PricebookCondition::VARIABLE_TYPE_CUSTOMER_FIELD) && (isset ($customer->column_fields [ $variableName ]))) {
				$variableValue = $customer->column_fields [ $variableName ];
			} else if (($variableType == PricebookCondition::VARIABLE_TYPE_SYSTEM_VARIABLE) && (isset ($systemVariables [ $variableName ]))) {
				$variableValue = $systemVariables [ $variableName ];
			} else {
				return false;
			}

			return $this->compare ($variableValue, $comparator, $value);
		}

		/**
		 * @param PricebookConditionGroup $conditionGroup
		 * @param clientes $customer
		 * @param array $systemVariables
		 *
		 * @return boolean
		 */
		private function evaluateConditionGroup ($conditionGroup, clientes $customer, array $systemVariables) {
			if ((empty ($conditionGroup)) || (empty ($conditionGroup->getConditions ()))) {
				return true;
			}

			$result     = false;
			$operator   = PricebookCondition::OPERATOR_OR;
			$conditions = $conditionGroup->getConditions ();
			foreach ($conditions as $condition) {
				if ($operator == PricebookCondition::OPERATOR_AND) {
					$result = !!($result && $this->evaluateCondition ($condition, $customer, $systemVariables));
				} else if ($operator == PricebookCondition::OPERATOR_OR) {
					$result = !!($result || $this->evaluateCondition ($condition, $customer, $systemVariables));
				}
				$operator = !empty ($condition->getOperator ()) ? $condition->getOperator () : PricebookCondition::OPERATOR_OR;
			}
			return $result;
		}

		/**
		 * @param PricebookConditionGroup[] $conditionGroups
		 * @param clientes $customer
		 * @param array $systemVariables
		 *
		 * @return boolean
		 */
		private function evaluateConditionGroups ($conditionGroups, clientes $customer, $systemVariables) {
			if (empty ($conditionGroups)) {
				return true;
			}

			$result   = false;
			$operator = PricebookConditionGroup::OPERATOR_OR;
			foreach ($conditionGroups as $group) {
				if ($operator == PricebookConditionGroup::OPERATOR_AND) {
					$result = !!($result && $this->evaluateConditionGroup ($group, $customer, $systemVariables));
				} else if ($operator == PricebookConditionGroup::OPERATOR_OR) {
					$result = !!($result || $this->evaluateConditionGroup ($group, $customer, $systemVariables));
				}
				$operator = !empty ($group->getOperator ()) ? $group->getOperator () : PricebookConditionGroup::OPERATOR_OR;
			}
			return $result;
		}

		/**
		 * @param integer $pricebookId
		 *
		 * @return PricebookConditionGroup[]|null
		 */
		private function fetchConditionGroups ($pricebookId) {
			if (empty ($pricebookId)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_pricebooks_conditiongroups WHERE pricebookid=? ORDER BY groupid', array ($pricebookId));
			if ($this->adb->num_rows ($result) > 0) {
				$groups = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$groups [] = PricebookConditionGroup::getInstance ()
						->setConditions ($this->fetchConditions ($row ['pricebookid'], $row ['groupid']))
						->setId ($row ['groupid'])
						->setOperator ($row ['operator'])
						->setPricebookId ($row ['pricebookid']);
				}
			} else {
				$groups = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $groups;
		}

		/**
		 * @param integer $pricebookId
		 * @param integer $groupId
		 *
		 * @return PricebookCondition[]|null
		 */
		private function fetchConditions ($pricebookId, $groupId) {
			$result = $this->adb->pquery (
				'SELECT
					pbc.*,
					IFNULL(f.fieldlabel, pbc.variablename) AS variablelabel
				FROM
					vtiger_pricebooks_conditions pbc
					LEFT JOIN vtiger_field f ON f.fieldname=pbc.variablename AND f.tabid IN (SELECT tabid FROM vtiger_tab t WHERE t.name=?)
				WHERE
					pbc.pricebookid=? AND
					pbc.groupid=?
				ORDER BY
					pbc.conditionid',
				array ('articulos', $pricebookId, $groupId)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$conditions = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$conditions [] = PricebookCondition::getInstance ()
						->setComparator ($row ['comparator'])
						->setGroupId ($row ['groupid'])
						->setId ($row ['conditionid'])
						->setOperator ($row ['operator'])
						->setPricebookId ($row ['pricebookid'])
						->setValue ($row ['value'])
						->setVariableName ($row ['variablename'])
						->setVariableType ($row ['variabletype']);
				}
			} else {
				$conditions = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $conditions;
		}

		/**
		 * @param string $instanceCode
		 *
		 * @return clientes|null
		 */
		private function fetchCustomer ($instanceCode) {
			if (empty ($instanceCode)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_instances WHERE code=?', array ($instanceCode));
			if ($this->adb->num_rows ($result) > 0) {
				$row       = $this->adb->fetchByAssoc ($result, -1, false);
				$accountId = $row ['accountid'];
				$customer  = PlatformUtils::getCrmEntity ($this->adb, 'clientes', $accountId);
			} else {
				$customer = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $customer;
		}

		/**
		 * @param Pricebook $pricebook
		 *
		 * @throws PricebookConditionGroupException
		 */
		private function saveConditionGroups ($pricebook) {
			$this->validateConditionGroups ($pricebook);
			$pricebookId = $pricebook->getId ();
			$groups      = $pricebook->getConditionGroups ();
			if (empty ($groups)) {
				$this->adb->pquery ('DELETE FROM vtiger_pricebooks_conditions WHERE pricebookid=?', array ($pricebookId));
				$this->adb->pquery ('DELETE FROM vtiger_pricebooks_conditiongroups WHERE pricebookid=?', array ($pricebookId));
				return;
			}

			$groupId           = 1;
			$processedGroupIds = array ();
			foreach ($groups as $group) {
				$operator = !empty ($group->getOperator ()) ? $group->getOperator () : null;
				$result   = $this->adb->pquery ('SELECT * FROM vtiger_pricebooks_conditiongroups WHERE pricebookid=? AND groupid=?', array ($pricebookId, $groupId));
				if ($this->adb->num_rows ($result) == 0) {
					$this->adb->pquery (
						'INSERT INTO vtiger_pricebooks_conditiongroups (pricebookid, groupid, operator) VALUES (?, ?, ?)',
						array ($pricebookId, $groupId, $operator)
					);
				} else {
					$this->adb->pquery (
						'UPDATE vtiger_pricebooks_conditiongroups SET operator=? WHERE pricebookid=? AND groupid=?',
						array ($operator, $pricebookId, $groupId)
					);
				}
				$group->setId ($groupId);
				$this->saveConditions ($group);
				$processedGroupIds [] = $groupId;
				$groupId++;
				DatabaseUtils::closeResult ($result);
				$result = null;
			}
			if (count ($processedGroupIds) > 0) {
				$questionMarks = str_repeat ('?, ', (count ($processedGroupIds) - 1)) . '?';
				$this->adb->pquery ("DELETE FROM vtiger_pricebooks_conditiongroups WHERE pricebookid=? AND groupid NOT IN ({$questionMarks})", array_merge (array ($pricebookId), $processedGroupIds));
			}
		}

		/**
		 * @param PricebookConditionGroup $group
		 */
		private function saveConditions ($group) {
			if (!($group instanceof PricebookConditionGroup)) {
				return;
			}

			$conditions            = $group->getConditions ();
			$pricebookId           = $group->getPricebookId ();
			$groupId               = $group->getId ();
			$conditionId           = 1;
			$processedConditionIds = array ();
			foreach ($conditions as $condition) {
				$operator = !empty ($condition->getOperator ()) ? $condition->getOperator () : null;
				$value    = ($condition->getValue () !== null) && (trim ($condition->getValue ()) !== '') ? $condition->getValue () : null;
				$result   = $this->adb->pquery ('SELECT * FROM vtiger_pricebooks_conditions WHERE pricebookid=? AND groupid=? AND conditionid=?', array ($pricebookId, $groupId, $conditionId));
				if ($this->adb->num_rows ($result) == 0) {
					$this->adb->pquery (
						'INSERT INTO vtiger_pricebooks_conditions (pricebookid, groupid, conditionid, variabletype, variablename, comparator, value, operator) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
						array ($pricebookId, $groupId, $conditionId, $condition->getVariableType (), $condition->getVariableName (), $condition->getComparator (), $value, $operator)
					);
				} else {
					$this->adb->pquery (
						'UPDATE vtiger_pricebooks_conditions SET variabletype=?, variablename=?, comparator=?, value=?, operator=? WHERE pricebookid=? AND groupid=? AND conditionid=?',
						array ($condition->getVariableType (), $condition->getVariableName (), $condition->getComparator (), $value, $operator, $pricebookId, $groupId, $conditionId)
					);
				}
				$condition->setId ($conditionId);
				$processedConditionIds [] = $conditionId;
				$conditionId++;
				DatabaseUtils::closeResult ($result);
				$result = null;
			}
			if (count ($processedConditionIds) > 0) {
				$questionMarks = str_repeat ('?, ', (count ($processedConditionIds) - 1)) . '?';
				$this->adb->pquery ("DELETE FROM vtiger_pricebooks_conditions WHERE pricebookid=? AND groupid=? AND conditionid NOT IN ({$questionMarks})", array_merge (array ($pricebookId, $groupId), $processedConditionIds));
			}
		}

		/**
		 * @param Pricebook $pricebook
		 *
		 * @throws PricebookException
		 */
		private function validate ($pricebook) {
			if ((empty ($pricebook)) || (!($pricebook instanceof Pricebook))) {
				throw new PricebookException (PricebookException::ERROR_PRICEBOOK_EMPTY);
			}

			$pricebook->validate ();

			$result = $this->adb->pquery ('SELECT * FROM vtiger_pricebooks WHERE pricebookname=?', array ($pricebook->getName ()));
			if ($this->adb->num_rows ($result) > 0) {
				$row         = $this->adb->fetchByAssoc ($result, -1, false);
				$pricebookId = $pricebook->getId ();
				if ((empty ($pricebookId)) || ($row ['pricebookid'] != $pricebookId)) {
					$e = new PricebookException (PricebookException::ERROR_PRICEBOOK_DUPLICATE_NAME);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($e)) {
				throw $e;
			}
		}

		/**
		 * @param PricebookCondition $condition
		 * @param integer $iteration
		 * @param integer $totalConditions
		 *
		 * @throws PricebookConditionException
		 */
		private function validateCondition ($condition, $iteration, $totalConditions) {
			$condition->validate ();
			if ((!in_array ($condition->getComparator (), array (PricebookCondition::COMPARATOR_EQUALS, PricebookCondition::COMPARATOR_NOT_EQUALS))) && (empty ($condition->getValue ()))) {
				throw new PricebookConditionException (PricebookConditionException::ERROR_PRICEBOOK_CONDITION_EMPTY_VALUE);
			}
			if (($iteration < $totalConditions) && (empty ($condition->getOperator ()))) {
				throw new PricebookConditionException (PricebookConditionException::ERROR_PRICEBOOK_CONDITION_EMPTY_OPERATOR);
			}
		}

		/**
		 * @param Pricebook $pricebook
		 *
		 * @throws PricebookConditionException
		 * @throws PricebookConditionGroupException
		 */
		private function validateConditionGroups ($pricebook) {
			$groups         = $pricebook->getConditionGroups ();
			$groupIteration = 1;
			foreach ($groups as $group) {
				$group->setPricebookId ($pricebook->getId ());
				$conditionIteration = 1;
				$conditions         = $group->getConditions ();
				foreach ($conditions as $condition) {
					$condition->setPricebookId ($pricebook->getId ());
					$this->validateCondition ($condition, $conditionIteration, count ($conditions));
					$conditionIteration++;
				}
				if (($groupIteration < count ($groups)) && (empty ($group->getOperator ()))) {
					throw new PricebookConditionGroupException (PricebookConditionGroupException::ERROR_PRICEBOOK_CONDITION_GROUP_EMPTY_OPERATOR);
				}
				$groupIteration++;
			}
		}

		/**
		 * @param Pricebook $pricebook
		 */
		public function deletePricebook ($pricebook) {
			if ((empty ($pricebook)) || (empty ($pricebook->getId ()))) {
				return;
			}

			$this->adb->startTransaction ();
			$this->adb->pquery ('DELETE FROM vtiger_pricebooks_conditions WHERE pricebookid=?', array ($pricebook->getId ()));
			$this->adb->pquery ('DELETE FROM vtiger_pricebooks_conditiongroups WHERE pricebookid=?', array ($pricebook->getId ()));
			$this->adb->pquery ('DELETE FROM vtiger_pricebooks WHERE pricebookid=?', array ($pricebook->getId ()));
			$this->adb->completeTransaction ();
		}

		/**
		 * @return null|Pricebook
		 */
		public function fetchDefaultPricebook () {
			$result = $this->adb->pquery ('SELECT * FROM vtiger_pricebooks WHERE isdefault=? ORDER BY pricebookid LIMIT 1', array (1));
			if ($this->adb->num_rows ($result) > 0) {
				$row       = $this->adb->fetchByAssoc ($result, -1, false);
				$pricebook = Pricebook::getInstance ()
					->setConditionGroups ($this->fetchConditionGroups ($row ['pricebookid']))
					->setDefault ($row ['isdefault'] == 1)
					->setDescription ($row ['description'])
					->setId ($row ['pricebookid'])
					->setMultiplier ($row ['multiplier'])
					->setName ($row ['pricebookname']);
			} else {
				$pricebook = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $pricebook;
		}

		/**
		 * @param integer $id
		 *
		 * @return null|Pricebook
		 */
		public function fetchPricebook ($id) {
			if (empty ($id)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_pricebooks WHERE pricebookid=?', array ($id));
			if ($this->adb->num_rows ($result) > 0) {
				$row       = $this->adb->fetchByAssoc ($result, -1, false);
				$pricebook = Pricebook::getInstance ()
					->setConditionGroups ($this->fetchConditionGroups ($id))
					->setDefault ($row ['isdefault'] == 1)
					->setDescription ($row ['description'])
					->setId ($row ['pricebookid'])
					->setMultiplier ($row ['multiplier'])
					->setName ($row ['pricebookname']);
			} else {
				$pricebook = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $pricebook;
		}

		/**
		 * @param integer $page
		 *
		 * @return array
		 */
		public function fetchPricebooks ($page = 1) {
			if ((empty ($page)) || ($page <= 0)) {
				$startRecord = 0;
			} else {
				$startRecord = (($page - 1) * self::RECORDS_PER_PAGE);
			}

			$limit = self::RECORDS_PER_PAGE;

			$result = $this->adb->query (
				"SELECT
					pb.*,
					total.__total_records__
				FROM
					vtiger_pricebooks pb
					CROSS JOIN (SELECT COUNT(*) AS __total_records__ FROM vtiger_pricebooks) AS total
				ORDER BY
					pb.pricebookname
				LIMIT {$startRecord}, {$limit}"
			);
			if ($this->adb->num_rows ($result) > 0) {
				$startRecord++;
				$totalRecords = null;
				$records      = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					if ($totalRecords === null) {
						$totalRecords = intval ($row ['__total_records__']);
					}
					$records [] = Pricebook::getInstance ()
						->setConditionGroups ($this->fetchConditionGroups ($row ['pricebookid']))
						->setDefault ($row ['isdefault'] == 1)
						->setDescription ($row ['description'])
						->setId ($row ['pricebookid'])
						->setMultiplier ($row ['multiplier'])
						->setName ($row ['pricebookname']);
				}
				$endRecord  = count ($records);
				$totalPages = ceil ($totalRecords / self::RECORDS_PER_PAGE);
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
		 * @param string $instanceCode
		 *
		 * @return null|Pricebook
		 */
		public function getApplicablePricebook ($instanceCode) {
			$customer = $this->fetchCustomer ($instanceCode);
			if (empty ($customer)) {
				return null;
			}

			$pricebook = null;
			$result    = $this->adb->query ('SELECT * FROM vtiger_pricebooks ORDER BY pricebookid');
			if ($this->adb->num_rows ($result) > 0) {
				$systemVariables     = SystemVariables::getAvailableVariableValues ($this->adb, null);
				$defaultPricebook    = null;
				$applicablePricebook = null;
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					if ($row ['isdefault'] == 1) {
						$defaultPricebook = Pricebook::getInstance ()
							->setConditionGroups (null)
							->setDefault ($row ['isdefault'] == 1)
							->setDescription ($row ['description'])
							->setId ($row ['pricebookid'])
							->setMultiplier ($row ['multiplier'])
							->setName ($row ['pricebookname']);
						continue;
					}

					$conditionGroups = $this->fetchConditionGroups ($row ['pricebookid']);
					if ($this->evaluateConditionGroups ($conditionGroups, $customer, $systemVariables)) {
						$applicablePricebook = Pricebook::getInstance ()
							->setConditionGroups ($conditionGroups)
							->setDefault ($row ['isdefault'] == 1)
							->setDescription ($row ['description'])
							->setId ($row ['pricebookid'])
							->setMultiplier ($row ['multiplier'])
							->setName ($row ['pricebookname']);
						break;
					}
				}
				if ($applicablePricebook !== null) {
					$pricebook = $applicablePricebook;
				} else if ($defaultPricebook !== null) {
					$pricebook = $defaultPricebook;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $pricebook;
		}

		/**
		 * @param Pricebook $pricebook
		 *
		 * @return Pricebook
		 * @throws PricebookException
		 */
		public function savePricebook ($pricebook) {
			if ((empty ($pricebook)) || (!($pricebook instanceof Pricebook))) {
				return null;
			}
			$this->validate ($pricebook);

			$pricebookId = $pricebook->getId ();
			$this->adb->startTransaction ();
			if ($pricebook->isDefault ()) {
				$this->adb->pquery ('UPDATE vtiger_pricebooks SET isdefault=?', array (0));
			}

			if (empty ($pricebookId)) {
				$this->adb->pquery (
					'INSERT INTO vtiger_pricebooks (pricebookname, description, multiplier, isdefault) VALUES (?, ?, ?, ?)',
					array ($pricebook->getName (), $pricebook->getDescription (), $pricebook->getMultiplier (), $pricebook->isDefault ())
				);
				$pricebookId = intval ($this->adb->getLastInsertID ());
				$pricebook->setId ($pricebookId);
			} else {
				$this->adb->pquery (
					'UPDATE vtiger_pricebooks SET pricebookname=?, description=?, multiplier=?, isdefault=? WHERE pricebookid=?',
					array ($pricebook->getName (), $pricebook->getDescription (), $pricebook->getMultiplier (), $pricebook->isDefault (), $pricebookId)
				);
			}
			$this->saveConditionGroups ($pricebook);
			$this->adb->completeTransaction ();
			return $pricebook;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return PricebookManager
		 */
		public static function getInstance (PearDatabase $adb) {
			if (self::$INSTANCE === null) {
				self::$INSTANCE = new self ($adb);
			}
			return self::$INSTANCE;
		}

	}
