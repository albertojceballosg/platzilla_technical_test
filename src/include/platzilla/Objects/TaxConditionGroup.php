<?php
	require_once ('include/platzilla/Exceptions/TaxConditionGroupException.php');
	require_once ('include/platzilla/Objects/TaxCondition.php');
	require_once ('include/platzilla/Objects/TaxConditionGroupInterface.php');

	class TaxConditionGroup implements TaxConditionGroupInterface, Serializable {
		/** @var integer */
		private $id;

		/** @var TaxCondition[] */
		private $conditions;

		/** @var string */
		private $operator;

		/** @var integer */
		private $taxId;

		/**
		 * @throws TaxConditionException
		 * @throws TaxConditionGroupException
		 */
		private function validateConditions () {
			if (empty ($this->conditions)) {
				throw new TaxConditionGroupException (TaxConditionGroupException::ERROR_TAX_CONDITION_GROUP_EMPTY_CONDITIONS);
			}
			foreach ($this->conditions as $condition) {
				if (empty ($condition)) {
					throw new TaxConditionGroupException (TaxConditionGroupException::ERROR_TAX_CONDITION_GROUP_EMPTY_CONDITION);
				} else if (!($condition instanceof TaxCondition)) {
					throw new TaxConditionGroupException (TaxConditionGroupException::ERROR_TAX_CONDITION_GROUP_INVALID_CONDITION);
				} else {
					$condition->validate ();
				}
			}
		}

		/**
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * @return TaxCondition[]
		 */
		public function getConditions () {
			return $this->conditions;
		}

		/**
		 * @return string
		 */
		public function getOperator () {
			return $this->operator;
		}

		/**
		 * @return integer
		 */
		public function getTaxId () {
			return $this->taxId;
		}

		/**
		 * @param integer $id
		 *
		 * @return TaxConditionGroup
		 */
		public function setId ($id) {
			if ((is_numeric ($id)) && ($id > 0) && (intval ($id) == $id)) {
				$this->id = intval ($id);
			} else {
				$this->id = null;
			}
			return $this;
		}

		/**
		 * @param TaxCondition[] $conditions
		 *
		 * @return TaxConditionGroup
		 */
		public function setConditions ($conditions) {
			if ((is_array ($conditions)) && (!empty ($conditions))) {
				$this->conditions = $conditions;
			} else {
				$this->conditions = null;
			}
			return $this;
		}

		/**
		 * @param string $operator
		 *
		 * @return TaxConditionGroup
		 */
		public function setOperator ($operator) {
			if (in_array ($operator, self::getAvailableOperators ())) {
				$this->operator = $operator;
			} else {
				$this->operator = null;
			}
			return $this;
		}

		/**
		 * @param integer $taxId
		 *
		 * @return TaxConditionGroup
		 */
		public function setTaxId ($taxId) {
			if ((is_numeric ($taxId)) && ($taxId > 0) && (intval ($taxId) == $taxId)) {
				$this->taxId = intval ($taxId);
			} else {
				$this->taxId = null;
			}
			return $this;
		}

		public function validate () {
			if (empty ($this->id)) {
				throw new TaxConditionGroupException (TaxConditionGroupException::ERROR_TAX_CONDITION_GROUP_EMPTY_ID);
			}
			$this->validateConditions ();
		}

		/**
		 * @return string
		 */
		public function serialize () {
			$conditions = $this->conditions;
			if (!empty ($conditions)) {
				$serializedConditions = array ();
				foreach ($conditions as $condition) {
					$serializedConditions [] = $condition->serialize ();
				}
			} else {
				$serializedConditions = null;
			}

			return serialize (
				array (
					$this->id,
					$this->operator,
					$this->taxId,
					$serializedConditions,
				)
			);
		}

		/**
		 * @param string $serialized
		 */
		public function unserialize ($serialized) {
			list (
				$this->id,
				$this->operator,
				$this->taxId,
				$serializedConditions,
				) = unserialize ($serialized);

			if (!empty ($serializedConditions)) {
				$this->conditions = array ();
				foreach ($serializedConditions as $serializedCondition) {
					$condition = TaxCondition::getInstance ();
					$condition->unserialize ($serializedCondition);
					$this->conditions [] = $condition;
				}
			}
		}

		/**
		 * @return string[]
		 */
		public static function getAvailableOperators () {
			return array (self::OPERATOR_AND, self::OPERATOR_OR);
		}

		/**
		 * @return TaxConditionGroup
		 */
		public static function getInstance () {
			return new self ();
		}

	}
