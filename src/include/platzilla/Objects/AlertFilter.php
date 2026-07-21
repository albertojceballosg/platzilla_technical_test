<?php
	require_once ('include/platzilla/Exceptions/FilterException.php');
	require_once ('include/platzilla/Objects/FilterInterface.php');
	
	class AlertFilter {
		
		/** @var integer */
		private $alertId;
		
		/** @var string */
		private $comparator;
		
		/** @var string */
		private$fieldName;
		
		/** @var integer */
		private $groupId;
		
		/** @var string */
		private $label;
		
		/** @var string */
		private $moduleName;
		
		/** @var string */
		private $operator;
		
		/** @var integer */
		private $sequence;
		
		/** @var string */
		private $value;
		
		/**
		 * @return integer
		 */
		public function getAlertId () {
			return $this->alertId;
		}
		
		/**
		 * @return string
		 */
		public function getComparator () {
			return $this->comparator;
		}
		
		/**
		 * @return string
		 */
		public function getFieldName () {
			return $this->fieldName;
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
		public function getLabel () {
			return $this->label;
		}
		
		/**
		 * @return string
		 */
		public function getModuleName () {
			return $this->moduleName;
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
		public function getSequence () {
			return $this->sequence;
		}
		
		/**
		 * @return string
		 */
		public function getValue () {
			return $this->value;
		}
		
		/**
		 * @param $alertId
		 *
		 * @return AlertFilter
		 */
		public function setAlertId ($alertId) {
			$this->alertId = $alertId;
			return $this;
		}
		
		/**
		 * @param string $comparator
		 *
		 * @return AlertFilter
		 */
		public function setComparator ($comparator) {
			if (in_array ($comparator, $this->getAvailableComparators ())) {
				$this->comparator = $comparator;
			}
			return $this;
		}
		
		/**
		 * @param string $fieldName
		 *
		 * @return AlertFilter
		 */
		public function setFieldName ($fieldName) {
			$this->fieldName = $fieldName;
			return $this;
		}
		
		/**
		 * @param integer $groupId
		 *
		 * @return AlertFilter
		 */
		public function setGroupId ($groupId) {
			$this->groupId = $groupId;
			return $this;
		}
		
		/**
		 * @param string $label
		 *
		 * @return AlertFilter
		 */
		public function setLabel ($label) {
			$this->label = $label;
			return $this;
		}
		
		/**
		 * @param string $moduleName
		 *
		 * @return AlertFilter
		 */
		public function setModuleName ($moduleName) {
			$this->moduleName = $moduleName;
			return $this;
		}
		
		/**
		 * @param string $operator
		 *
		 * @return AlertFilter
		 */
		public function setOperator ($operator) {
			if (in_array ($operator, array (FilterInterface::OPERATOR_AND, FilterInterface::OPERATOR_OR))) {
				$this->operator = $operator;
			}
			return $this;
		}
		
		/**
		 * @param integer $sequence
		 *
		 * @return AlertFilter
		 */
		public function setSequence ($sequence) {
			$this->sequence = $sequence;
			return $this;
		}
		
		/**
		 * @param string $value
		 *
		 * @return AlertFilter
		 */
		public function setValue ($value) {
			$this->value = $value;
			return $this;
		}
		
		/**
		 * @throws FilterException
		 */
		public function validate () {
			if (empty ($this->comparator)) {
				throw new FilterException (FilterException::ERROR_FILTER_EMPTY_COMPARATOR);
			} else if (empty ($this->fieldName)) {
				throw new FilterException (FilterException::ERROR_FILTER_EMPTY_FIELD_NAME);
			} else if (empty ($this->label)) {
				throw new FilterException (FilterException::ERROR_FILTER_EMPTY_LABEL);
			} else if (!isset ($this->sequence)) {
				throw new FilterException (FilterException::ERROR_FILTER_EMPTY_SEQUENCE);
			}
		}
		
		/**
		 * @return string[]
		 */
		public static function getAvailableComparators () {
			return array (FilterInterface::COMPARATOR_CONTAINS, FilterInterface::COMPARATOR_DAYS_AFTER, FilterInterface::COMPARATOR_DAYS_AFTER_EXACT, FilterInterface::COMPARATOR_DAYS_BEFORE, FilterInterface::COMPARATOR_DAYS_BEFORE_EXACT, FilterInterface::COMPARATOR_DOES_NOT_CONTAIN, FilterInterface::COMPARATOR_ENDS_WITH, FilterInterface::COMPARATOR_EQUALS, FilterInterface::COMPARATOR_GREATER, FilterInterface::COMPARATOR_GREATER_OR_EQUALS, FilterInterface::COMPARATOR_LESS, FilterInterface::COMPARATOR_LESS_OR_EQUALS, FilterInterface::COMPARATOR_NOT_EQUALS, FilterInterface::COMPARATOR_STARTS_WITH);
		}
		
		/**
		 * @return AlertFilter
		 */
		public static function getInstance () {
			return new self ();
		}
		
	}