<?php
	require_once ('include/platzilla/Exceptions/FilterException.php');
	require_once ('include/platzilla/Objects/FilterInterface.php');

	abstract class Filter implements FilterInterface {
		/** @var string */
		protected $comparator;

		/** @var string */
		protected $fieldName;

		/** @var integer */
		protected $groupId;

		/** @var string */
		protected $label;

		/** @var string */
		protected $moduleName;

		/** @var string */
		protected $operator;

		/** @var integer */
		protected $sequence;

		/** @var string */
		protected $value;

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
		 * @param string $comparator
		 *
		 * @return Filter
		 */
		public function setComparator ($comparator) {
			if (in_array ($comparator, self::getAvailableComparators ())) {
				$this->comparator = $comparator;
			}
			return $this;
		}

		/**
		 * @param string $fieldName
		 *
		 * @return Filter
		 */
		public function setFieldName ($fieldName) {
			$this->fieldName = $fieldName;
			return $this;
		}

		/**
		 * @param integer $groupId
		 *
		 * @return Filter
		 */
		public function setGroupId ($groupId) {
			$this->groupId = $groupId;
			return $this;
		}

		/**
		 * @param string $label
		 *
		 * @return Filter
		 */
		public function setLabel ($label) {
			$this->label = $label;
			return $this;
		}

		/**
		 * @param string $moduleName
		 *
		 * @return Filter
		 */
		public function setModuleName ($moduleName) {
			$this->moduleName = $moduleName;
			return $this;
		}

		/**
		 * @param string $operator
		 *
		 * @return Filter
		 */
		public function setOperator ($operator) {
			if (in_array ($operator, array (self::OPERATOR_AND, self::OPERATOR_OR))) {
				$this->operator = $operator;
			}
			return $this;
		}

		/**
		 * @param integer $sequence
		 *
		 * @return Filter
		 */
		public function setSequence ($sequence) {
			$this->sequence = $sequence;
			return $this;
		}

		/**
		 * @param string $value
		 *
		 * @return Filter
		 */
		public function setValue ($value) {
			$this->value = $value;
			return $this;
		}

		/**
		 * @param Filter $filter
		 */
		public function copyValuesFrom ($filter) {
			if ((empty ($filter)) || (!($filter instanceof Filter)) || ($this->isEqualTo ($filter))) {
				return;
			}

			$this->comparator = $filter->getComparator ();
			$this->fieldName  = $filter->getFieldName ();
			$this->label      = $filter->getLabel ();
			$this->moduleName = $filter->getModuleName ();
			$this->operator   = $filter->getOperator ();
			$this->sequence   = $filter->getSequence ();
			$this->value      = $filter->getValue ();
		}

		/**
		 * @param Filter $filter
		 *
		 * @return boolean
		 */
		public function isEqualTo ($filter) {
			if (
				(empty ($filter)) ||
				(!($filter instanceof Filter)) ||
				($this->comparator != $filter->getComparator ()) ||
				($this->fieldName != $filter->getFieldName ()) ||
				($this->label != $filter->getLabel ()) ||
				($this->moduleName != $filter->getModuleName ()) ||
				($this->operator != $filter->getOperator ()) ||
				($this->sequence != $filter->getSequence ()) ||
				($this->value != $filter->getValue ())
			) {
				return false;
			} else {
				return true;
			}
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
			return array (self::COMPARATOR_CONTAINS, self::COMPARATOR_DAYS_AFTER, self::COMPARATOR_DAYS_AFTER_EXACT, self::COMPARATOR_DAYS_BEFORE, self::COMPARATOR_DAYS_BEFORE_EXACT, self::COMPARATOR_DOES_NOT_CONTAIN, self::COMPARATOR_ENDS_WITH, self::COMPARATOR_EQUALS, self::COMPARATOR_GREATER, self::COMPARATOR_GREATER_OR_EQUALS, self::COMPARATOR_LESS, self::COMPARATOR_LESS_OR_EQUALS, self::COMPARATOR_NOT_EQUALS, self::COMPARATOR_STARTS_WITH);
		}

	}
