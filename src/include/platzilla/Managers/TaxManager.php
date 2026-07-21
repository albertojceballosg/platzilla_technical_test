<?php
	require_once ('include/platzilla/Objects/Tax.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/PlatformUtils.class.php');

	class TaxManager {
		const RECORDS_PER_PAGE = 25;

		/** @var TaxManager */
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
				case TaxCondition::COMPARATOR_EQUALS:
					$result = ($value === null) ? (($variableValue === null) || ($variableValue === '')) : ($variableValue == $value);
					break;
				case TaxCondition::COMPARATOR_GREATER:
					$result = ($variableValue > $value);
					break;
				case TaxCondition::COMPARATOR_GREATER_OR_EQUALS:
					$result = ($variableValue >= $value);
					break;
				case TaxCondition::COMPARATOR_LESS:
					$result = ($variableValue < $value);
					break;
				case TaxCondition::COMPARATOR_LESS_OR_EQUALS:
					$result = ($variableValue <= $value);
					break;
				case TaxCondition::COMPARATOR_NOT_EQUALS:
					$result = ($value === null) ? (($variableValue !== null) && ($variableValue !== '')) : ($variableValue != $value);
					break;
				case TaxCondition::COMPARATOR_CONTAINS:
					$result = (preg_match ("/{$value}/", $variableValue) == 1);
					break;
				case TaxCondition::COMPARATOR_DOES_NOT_CONTAIN:
					$result = (preg_match ("/{$value}/", $variableValue) == 0);
					break;
				default:
					$result = false;
					break;
			}
			return !!$result;
		}

		/**
		 * @param TaxCondition $condition
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

			if (($variableType == TaxCondition::VARIABLE_TYPE_CUSTOMER_FIELD) && (isset ($customer->column_fields [ $variableName ]))) {
				$variableValue = $customer->column_fields [ $variableName ];
			} else if (($variableType == TaxCondition::VARIABLE_TYPE_SYSTEM_VARIABLE) && (isset ($systemVariables [ $variableName ]))) {
				$variableValue = $systemVariables [ $variableName ];
			} else {
				return false;
			}

			return $this->compare ($variableValue, $comparator, $value);
		}

		/**
		 * @param TaxConditionGroup $conditionGroup
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
			$operator   = TaxCondition::OPERATOR_OR;
			$conditions = $conditionGroup->getConditions ();
			foreach ($conditions as $condition) {
				if ($operator == TaxCondition::OPERATOR_AND) {
					$result = !!($result && $this->evaluateCondition ($condition, $customer, $systemVariables));
				} else if ($operator == TaxCondition::OPERATOR_OR) {
					$result = !!($result || $this->evaluateCondition ($condition, $customer, $systemVariables));
				}
				$operator = !empty ($condition->getOperator ()) ? $condition->getOperator () : TaxCondition::OPERATOR_OR;
			}
			return $result;
		}

		/**
		 * @param TaxConditionGroup[] $conditionGroups
		 * @param clientes $customer
		 * @param array $systemVariables
		 *
		 * @return boolean
		 */
		private function evaluateConditionGroups ($conditionGroups, clientes $customer, $systemVariables) {
			$result   = false;
			$operator = TaxConditionGroup::OPERATOR_OR;
			foreach ($conditionGroups as $group) {
				if ($operator == TaxConditionGroup::OPERATOR_AND) {
					$result = !!($result && $this->evaluateConditionGroup ($group, $customer, $systemVariables));
				} else if ($operator == TaxConditionGroup::OPERATOR_OR) {
					$result = !!($result || $this->evaluateConditionGroup ($group, $customer, $systemVariables));
				}
				$operator = !empty ($group->getOperator ()) ? $group->getOperator () : TaxConditionGroup::OPERATOR_OR;
			}
			return $result;
		}

		/**
		 * @param integer $taxId
		 *
		 * @return TaxConditionGroup[]|null
		 */
		private function fetchConditionGroups ($taxId) {
			if (empty ($taxId)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_taxes_conditiongroups WHERE taxid=? ORDER BY groupid', array ($taxId));
			if ($this->adb->num_rows ($result) > 0) {
				$groups = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$groups [] = TaxConditionGroup::getInstance ()
						->setConditions ($this->fetchConditions ($row ['taxid'], $row ['groupid']))
						->setId ($row ['groupid'])
						->setOperator ($row ['operator'])
						->setTaxId ($row ['taxid']);
				}
			} else {
				$groups = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $groups;
		}

		/**
		 * @param integer $taxId
		 * @param integer $groupId
		 *
		 * @return TaxCondition[]|null
		 */
		private function fetchConditions ($taxId, $groupId) {
			$result = $this->adb->pquery (
				'SELECT
					tc.*
				FROM
					vtiger_taxes_conditions tc
				WHERE
					tc.taxid=? AND
					tc.groupid=?
				ORDER BY
					tc.conditionid',
				array ($taxId, $groupId)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$conditions = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$conditions [] = TaxCondition::getInstance ()
						->setComparator ($row ['comparator'])
						->setGroupId ($row ['groupid'])
						->setId ($row ['conditionid'])
						->setOperator ($row ['operator'])
						->setTaxId ($row ['taxid'])
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
		 * @param Tax $tax
		 *
		 * @throws TaxConditionGroupException
		 */
		private function saveConditionGroups ($tax) {
			$this->validateConditionGroups ($tax);
			$taxId  = $tax->getId ();
			$groups = $tax->getConditionGroups ();
			if (empty ($groups)) {
				$this->adb->pquery ('DELETE FROM vtiger_taxes_conditions WHERE taxid=?', array ($taxId));
				$this->adb->pquery ('DELETE FROM vtiger_taxes_conditiongroups WHERE taxid=?', array ($taxId));
				return;
			}

			$groupId           = 1;
			$processedGroupIds = array ();
			foreach ($groups as $group) {
				$operator = !empty ($group->getOperator ()) ? $group->getOperator () : null;
				$result   = $this->adb->pquery ('SELECT * FROM vtiger_taxes_conditiongroups WHERE taxid=? AND groupid=?', array ($taxId, $groupId));
				if ($this->adb->num_rows ($result) == 0) {
					$this->adb->pquery (
						'INSERT INTO vtiger_taxes_conditiongroups (taxid, groupid, operator) VALUES (?, ?, ?)',
						array ($taxId, $groupId, $operator)
					);
				} else {
					$this->adb->pquery (
						'UPDATE vtiger_taxes_conditiongroups SET operator=? WHERE taxid=? AND groupid=?',
						array ($operator, $taxId, $groupId)
					);
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
				$group->setId ($groupId);
				$this->saveConditions ($group);
				$processedGroupIds [] = $groupId;
				$groupId++;
			}
			if (count ($processedGroupIds) > 0) {
				$questionMarks = str_repeat ('?, ', (count ($processedGroupIds) - 1)) . '?';
				$this->adb->pquery ("DELETE FROM vtiger_taxes_conditiongroups WHERE taxid=? AND groupid NOT IN ({$questionMarks})", array_merge (array ($taxId), $processedGroupIds));
			}
		}

		/**
		 * @param TaxConditionGroup $group
		 */
		private function saveConditions ($group) {
			if (!($group instanceof TaxConditionGroup)) {
				return;
			}

			$conditions            = $group->getConditions ();
			$taxId                 = $group->getTaxId ();
			$groupId               = $group->getId ();
			$conditionId           = 1;
			$processedConditionIds = array ();
			foreach ($conditions as $condition) {
				$operator = !empty ($condition->getOperator ()) ? $condition->getOperator () : null;
				$value    = ($condition->getValue () !== null) && (trim ($condition->getValue ()) !== '') ? $condition->getValue () : null;
				$result   = $this->adb->pquery ('SELECT * FROM vtiger_taxes_conditions WHERE taxid=? AND groupid=? AND conditionid=?', array ($taxId, $groupId, $conditionId));
				if ($this->adb->num_rows ($result) == 0) {
					$this->adb->pquery (
						'INSERT INTO vtiger_taxes_conditions (taxid, groupid, conditionid, variabletype, variablename, comparator, value, operator) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
						array ($taxId, $groupId, $conditionId, $condition->getVariableType (), $condition->getVariableName (), $condition->getComparator (), $value, $operator)
					);
				} else {
					$this->adb->pquery (
						'UPDATE vtiger_taxes_conditions SET variabletype=?, variablename=?, comparator=?, value=?, operator=? WHERE taxid=? AND groupid=? AND conditionid=?',
						array ($condition->getVariableType (), $condition->getVariableName (), $condition->getComparator (), $value, $operator, $taxId, $groupId, $conditionId)
					);
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
				$condition->setId ($conditionId);
				$processedConditionIds [] = $conditionId;
				$conditionId++;
			}
			if (count ($processedConditionIds) > 0) {
				$questionMarks = str_repeat ('?, ', (count ($processedConditionIds) - 1)) . '?';
				$this->adb->pquery ("DELETE FROM vtiger_taxes_conditions WHERE taxid=? AND groupid=? AND conditionid NOT IN ({$questionMarks})", array_merge (array ($taxId, $groupId), $processedConditionIds));
			}
		}

		/**
		 * @param Tax $tax
		 *
		 * @throws TaxException
		 */
		private function validate ($tax) {
			if ((empty ($tax)) || (!($tax instanceof Tax))) {
				throw new TaxException (TaxException::ERROR_TAX_EMPTY);
			}

			$tax->validate ();

			$result = $this->adb->pquery ('SELECT * FROM vtiger_taxes WHERE taxname=?', array ($tax->getName ()));
			if ($this->adb->num_rows ($result) > 0) {
				$row   = $this->adb->fetchByAssoc ($result, -1, false);
				$taxId = $tax->getId ();
				if ((empty ($taxId)) || ($row ['taxid'] != $taxId)) {
					$e = new TaxException (TaxException::ERROR_TAX_DUPLICATE_NAME);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($e)) {
				throw $e;
			}
		}

		/**
		 * @param TaxCondition $condition
		 * @param integer $iteration
		 * @param integer $totalConditions
		 *
		 * @throws TaxConditionException
		 */
		private function validateCondition ($condition, $iteration, $totalConditions) {
			$condition->validate ();
			if ((!in_array ($condition->getComparator (), array (TaxCondition::COMPARATOR_EQUALS, TaxCondition::COMPARATOR_NOT_EQUALS))) && (empty ($condition->getValue ()))) {
				throw new TaxConditionException (TaxConditionException::ERROR_TAX_CONDITION_EMPTY_VALUE);
			}
			if (($iteration < $totalConditions) && (empty ($condition->getOperator ()))) {
				throw new TaxConditionException (TaxConditionException::ERROR_TAX_CONDITION_EMPTY_OPERATOR);
			}
		}

		/**
		 * @param Tax $tax
		 *
		 * @throws TaxConditionException
		 * @throws TaxConditionGroupException
		 */
		private function validateConditionGroups ($tax) {
			$groups         = $tax->getConditionGroups ();
			$groupIteration = 1;
			foreach ($groups as $group) {
				$group->setTaxId ($tax->getId ());
				$conditionIteration = 1;
				$conditions         = $group->getConditions ();
				foreach ($conditions as $condition) {
					$condition->setTaxId ($tax->getId ());
					$this->validateCondition ($condition, $conditionIteration, count ($conditions));
					$conditionIteration++;
				}
				if (($groupIteration < count ($groups)) && (empty ($group->getOperator ()))) {
					throw new TaxConditionGroupException (TaxConditionGroupException::ERROR_TAX_CONDITION_GROUP_EMPTY_OPERATOR);
				}
				$groupIteration++;
			}
		}

		/**
		 * @param Tax $tax
		 */
		public function deleteTax ($tax) {
			if ((empty ($tax)) || (empty ($tax->getId ()))) {
				return;
			}

			$this->adb->startTransaction ();
			$this->adb->pquery ('DELETE FROM vtiger_taxes_conditions WHERE taxid=?', array ($tax->getId ()));
			$this->adb->pquery ('DELETE FROM vtiger_taxes_conditiongroups WHERE taxid=?', array ($tax->getId ()));
			$this->adb->pquery ('DELETE FROM vtiger_taxes WHERE taxid=?', array ($tax->getId ()));
			$this->adb->completeTransaction ();
		}

		/**
		 * @return null|Tax
		 */
		public function fetchDefaultTax () {
			$result = $this->adb->pquery ('SELECT * FROM vtiger_taxes WHERE isdefault=?', array (1));
			if ($this->adb->num_rows ($result) > 0) {
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				$tax = Tax::getInstance ()
					->setConditionGroups ($this->fetchConditionGroups ($row ['taxid']))
					->setDefault ($row ['isdefault'] == 1)
					->setDescription ($row ['description'])
					->setId ($row ['taxid'])
					->setName ($row ['taxname'])
					->setPercentage ($row ['percentage']);
			} else {
				$tax = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $tax;
		}

		/**
		 * @param integer $id
		 *
		 * @return null|Tax
		 */
		public function fetchTax ($id) {
			if (empty ($id)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_taxes WHERE taxid=?', array ($id));
			if ($this->adb->num_rows ($result) > 0) {
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				$tax = Tax::getInstance ()
					->setConditionGroups ($this->fetchConditionGroups ($id))
					->setDefault ($row ['isdefault'] == 1)
					->setDescription ($row ['description'])
					->setId ($row ['taxid'])
					->setName ($row ['taxname'])
					->setPercentage ($row ['percentage']);
			} else {
				$tax = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $tax;
		}

		/**
		 * @param integer $page
		 *
		 * @return array
		 */
		public function fetchTaxes ($page = 1) {
			if ((empty ($page)) || ($page <= 0)) {
				$startRecord = 0;
			} else {
				$startRecord = (($page - 1) * self::RECORDS_PER_PAGE);
			}

			$limit = self::RECORDS_PER_PAGE;

			$result = $this->adb->query (
				"SELECT
					t.*,
					total.__total_records__
				FROM
					vtiger_taxes t
					CROSS JOIN (SELECT COUNT(*) AS __total_records__ FROM vtiger_taxes) AS total
				ORDER BY
					t.taxname
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
					$records [] = Tax::getInstance ()
						->setConditionGroups ($this->fetchConditionGroups ($row ['taxid']))
						->setDefault ($row ['isdefault'] == 1)
						->setDescription ($row ['description'])
						->setId ($row ['taxid'])
						->setName ($row ['taxname'])
						->setPercentage ($row ['percentage']);
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
		 * @return null|Tax
		 */
		public function getApplicableTax ($instanceCode) {
			if (empty ($instanceCode)) {
				return null;
			}

			$customer = $this->fetchCustomer ($instanceCode);
			if (empty ($customer)) {
				return null;
			}

			$tax    = null;
			$result = $this->adb->query ('SELECT * FROM vtiger_taxes ORDER BY taxid');
			if ($this->adb->num_rows ($result) > 0) {
				$systemVariables = SystemVariables::getAvailableVariableValues ($this->adb, null);
				$defaultTax      = null;
				$applicableTax   = null;
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$conditionGroups = $this->fetchConditionGroups ($row ['taxid']);
					if (empty ($conditionGroups)) {
						$defaultTax = Tax::getInstance ()
							->setConditionGroups (null)
							->setDefault ($row ['isdefault'] == 1)
							->setDescription ($row ['description'])
							->setId ($row ['taxid'])
							->setName ($row ['taxname'])
							->setPercentage ($row ['percentage']);
					} else if ($this->evaluateConditionGroups ($conditionGroups, $customer, $systemVariables)) {
						$applicableTax = Tax::getInstance ()
							->setConditionGroups ($conditionGroups)
							->setDefault ($row ['isdefault'] == 1)
							->setDescription ($row ['description'])
							->setId ($row ['taxid'])
							->setName ($row ['taxname'])
							->setPercentage ($row ['percentage']);
						break;
					}
				}
				if ($applicableTax !== null) {
					$tax = $applicableTax;
				} else if ($defaultTax !== null) {
					$tax = $defaultTax;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $tax;
		}

		/**
		 * @param Tax $tax
		 *
		 * @return Tax
		 * @throws TaxException
		 */
		public function saveTax ($tax) {
			if ((empty ($tax)) || (!($tax instanceof Tax))) {
				return null;
			}
			$this->validate ($tax);

			$taxId = $tax->getId ();
			$this->adb->startTransaction ();
			if (empty ($taxId)) {
				$this->adb->pquery (
					'INSERT INTO vtiger_taxes (taxname, description, percentage, isdefault) VALUES (?, ?, ?, ?)',
					array ($tax->getName (), $tax->getDescription (), $tax->getPercentage (), $tax->isDefault ())
				);
				$taxId = intval ($this->adb->getLastInsertID ());
				$tax->setId ($taxId);
			} else {
				$this->adb->pquery (
					'UPDATE vtiger_taxes SET taxname=?, description=?, percentage=?, isdefault=? WHERE taxid=?',
					array ($tax->getName (), $tax->getDescription (), $tax->getPercentage (), $tax->isDefault (), $taxId)
				);
			}
			$this->saveConditionGroups ($tax);
			$this->adb->completeTransaction ();
			return $tax;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return TaxManager
		 */
		public static function getInstance (PearDatabase $adb) {
			if (self::$INSTANCE === null) {
				self::$INSTANCE = new self ($adb);
			}
			return self::$INSTANCE;
		}

	}
