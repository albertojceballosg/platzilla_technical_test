<?php
	require_once ('include/platzilla/Exceptions/PricebookConditionGroupException.php');
	require_once ('include/platzilla/Objects/PricebookCondition.php');
	require_once ('include/platzilla/Objects/PricebookConditionGroupInterface.php');

	class PricebookConditionGroup implements PricebookConditionGroupInterface, Serializable {
		/** @var integer */
		private $id;

		/** @var PricebookCondition[] */
		private $conditions;

		/** @var string */
		private $operator;

		/** @var integer */
		private $pricebookId;

		/**
		 * @throws PricebookConditionException
		 * @throws PricebookConditionGroupException
		 */
		private function validateConditions () {
			if (empty ($this->conditions)) {
				throw new PricebookConditionGroupException (PricebookConditionGroupException::ERROR_PRICEBOOK_CONDITION_GROUP_EMPTY_CONDITIONS);
			}
			foreach ($this->conditions as $condition) {
				if (empty ($condition)) {
					throw new PricebookConditionGroupException (PricebookConditionGroupException::ERROR_PRICEBOOK_CONDITION_GROUP_EMPTY_CONDITION);
				} else if (!($condition instanceof PricebookCondition)) {
					throw new PricebookConditionGroupException (PricebookConditionGroupException::ERROR_PRICEBOOK_CONDITION_GROUP_INVALID_CONDITION);
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
		 * @return PricebookCondition[]
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
		public function getPricebookId () {
			return $this->pricebookId;
		}

		/**
		 * @param integer $id
		 *
		 * @return PricebookConditionGroup
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
		 * @param PricebookCondition[] $conditions
		 *
		 * @return PricebookConditionGroup
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
		 * @return PricebookConditionGroup
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
		 * @param integer $pricebookId
		 *
		 * @return PricebookConditionGroup
		 */
		public function setPricebookId ($pricebookId) {
			if ((is_numeric ($pricebookId)) && ($pricebookId > 0) && (intval ($pricebookId) == $pricebookId)) {
				$this->pricebookId = intval ($pricebookId);
			} else {
				$this->pricebookId = null;
			}
			return $this;
		}

		public function validate () {
			if (empty ($this->id)) {
				throw new PricebookConditionGroupException (PricebookConditionGroupException::ERROR_PRICEBOOK_CONDITION_GROUP_EMPTY_ID);
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
					$this->pricebookId,
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
				$this->pricebookId,
				$serializedConditions,
			) = unserialize ($serialized);

			if (!empty ($serializedConditions)) {
				$this->conditions = array ();
				foreach ($serializedConditions as $serializedCondition) {
					$condition = PricebookCondition::getInstance ();
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
		 * @return PricebookConditionGroup
		 */
		public static function getInstance () {
			return new self ();
		}

	}
