<?php
	require_once ('include/platzilla/Exceptions/TaxConditionException.php');
	require_once ('include/platzilla/Objects/TaxConditionInterface.php');

	class TaxCondition implements TaxConditionInterface, Serializable {
		/** @var integer */
		private $id;

		/** @var string */
		private $comparator;

		/** @var integer */
		private $groupId;

		/** @var string */
		private $operator;

		/** @var integer */
		private $taxId;

		/** @var string */
		private $variableName;

		/** @var string */
		private $variableType;

		/** @var string */
		private $value;

		/**
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * @return string
		 */
		public function getComparator () {
			return $this->comparator;
		}

		/**
		 * @return integer
		 */
		public function getGroupId () {
			return $this->groupId;
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
		 * @return string
		 */
		public function getVariableName () {
			return $this->variableName;
		}

		/**
		 * @return string
		 */
		public function getVariableType () {
			return $this->variableType;
		}

		/**
		 * @return string
		 */
		public function getValue () {
			return $this->value;
		}

		/**
		 * @param integer $id
		 *
		 * @return TaxCondition
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
		 * @param string $comparator
		 *
		 * @return TaxCondition
		 */
		public function setComparator ($comparator) {
			if (in_array ($comparator, self::getAvailableComparators ())) {
				$this->comparator = $comparator;
			} else {
				$this->comparator = null;
			}
			return $this;
		}

		/**
		 * @param integer $groupId
		 *
		 * @return TaxCondition
		 */
		public function setGroupId ($groupId) {
			if ((is_numeric ($groupId)) && ($groupId > 0) && (intval ($groupId) == $groupId)) {
				$this->groupId = intval ($groupId);
			} else {
				$this->groupId = null;
			}
			return $this;
		}

		/**
		 * @param string $operator
		 *
		 * @return TaxCondition
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
		 * @return TaxCondition
		 */
		public function setTaxId ($taxId) {
			if ((is_numeric ($taxId)) && ($taxId > 0) && (intval ($taxId) == $taxId)) {
				$this->taxId = intval ($taxId);
			} else {
				$this->taxId = null;
			}
			return $this;
		}

		/**
		 * @param string $variableName
		 *
		 * @return TaxCondition
		 */
		public function setVariableName ($variableName) {
			if (is_scalar ($variableName)) {
				$this->variableName = $variableName;
			} else {
				$this->variableName = null;
			}
			return $this;
		}

		/**
		 * @param string $variableType
		 *
		 * @return TaxCondition
		 */
		public function setVariableType ($variableType) {
			if (in_array ($variableType, self::getAvailableVariableTypes ())) {
				$this->variableType = $variableType;
			} else {
				$this->variableType = null;
			}
			return $this;
		}

		/**
		 * @param string $value
		 *
		 * @return TaxCondition
		 */
		public function setValue ($value) {
			if (is_scalar ($value)) {
				$this->value = $value;
			} else {
				$this->value = null;
			}
			return $this;
		}

		/**
		 * @throws TaxConditionException
		 */
		public function validate () {
			if (empty ($this->id)) {
				throw new TaxConditionException (TaxConditionException::ERROR_TAX_CONDITION_EMPTY_ID);
			} else if (empty ($this->comparator)) {
				throw new TaxConditionException (TaxConditionException::ERROR_TAX_CONDITION_EMPTY_COMPARATOR);
			} else if (empty ($this->groupId)) {
				throw new TaxConditionException (TaxConditionException::ERROR_TAX_CONDITION_EMPTY_GROUP_ID);
			} else if (empty ($this->variableName)) {
				throw new TaxConditionException (TaxConditionException::ERROR_TAX_CONDITION_EMPTY_VARIABLE_NAME);
			} else if (empty ($this->variableType)) {
				throw new TaxConditionException (TaxConditionException::ERROR_TAX_CONDITION_EMPTY_VARIABLE_TYPE);
			}
		}

		/**
		 * @return string
		 */
		public function serialize () {
			return serialize (
				array (
					$this->id,
					$this->comparator,
					$this->groupId,
					$this->operator,
					$this->taxId,
					$this->variableName,
					$this->variableType,
					$this->value,
				)
			);
		}

		/**
		 * @param string $serialized
		 */
		public function unserialize ($serialized) {
			list (
				$this->id,
				$this->comparator,
				$this->groupId,
				$this->operator,
				$this->taxId,
				$this->variableName,
				$this->variableType,
				$this->value,
			) = unserialize ($serialized);
		}

		/**
		 * @return string[]
		 */
		public static function getAvailableComparators () {
			return array (self::COMPARATOR_CONTAINS, self::COMPARATOR_DOES_NOT_CONTAIN, self::COMPARATOR_EQUALS, self::COMPARATOR_GREATER, self::COMPARATOR_GREATER_OR_EQUALS, self::COMPARATOR_LESS, self::COMPARATOR_LESS_OR_EQUALS, self::COMPARATOR_NOT_EQUALS);
		}

		/**
		 * @return string[]
		 */
		public static function getAvailableOperators () {
			return array (self::OPERATOR_AND, self::OPERATOR_OR);
		}

		/**
		 * @return string[]
		 */
		public static function getAvailableVariableTypes () {
			return array (self::VARIABLE_TYPE_CUSTOMER_FIELD, self::VARIABLE_TYPE_SYSTEM_VARIABLE);
		}

		/**
		 * @return TaxCondition
		 */
		public static function getInstance () {
			return new self ();
		}

	}
