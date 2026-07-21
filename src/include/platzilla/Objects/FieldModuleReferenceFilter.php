<?php
	require_once ('include/platzilla/Exceptions/FieldModuleReferenceFilterException.php');
	require_once ('include/platzilla/Objects/FilterInterface.php');

	class FieldModuleReferenceFilter implements FilterInterface {
		const TYPE_LITERAL      = 'LITERAL';
		const TYPE_SOURCE_FIELD = 'SOURCE FIELD';

		/** @var string */
		private $comparator;

		/** @var string */
		private $fieldName;

		/** @var string */
		private $operator;

		/** @var integer */
		private $sequence;

		/** @var string */
		private $value;

		/** @var string */
		private $valueType;

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
		 * @return string
		 */
		public function getValueModuleName () {
			return $this->valueModuleName;
		}

		/**
		 * @return string
		 */
		public function getValueType () {
			return $this->valueType;
		}

		/**
		 * @param string $comparator
		 *
		 * @return FieldModuleReferenceFilter
		 */
		public function setComparator ($comparator) {
			if (in_array ($comparator, array (self::COMPARATOR_CONTAINS, self::COMPARATOR_DOES_NOT_CONTAIN, self::COMPARATOR_ENDS_WITH, self::COMPARATOR_EQUALS, self::COMPARATOR_GREATER, self::COMPARATOR_GREATER_OR_EQUALS, self::COMPARATOR_LESS, self::COMPARATOR_LESS_OR_EQUALS, self::COMPARATOR_NOT_EQUALS, self::COMPARATOR_STARTS_WITH))) {
				$this->comparator = $comparator;
			}
			return $this;
		}

		/**
		 * @param string $fieldName
		 *
		 * @return FieldModuleReferenceFilter
		 */
		public function setFieldName ($fieldName) {
			$this->fieldName = $fieldName;
			return $this;
		}

		/**
		 * @param string $operator
		 *
		 * @return FieldModuleReferenceFilter
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
		 * @return FieldModuleReferenceFilter
		 */
		public function setSequence ($sequence) {
			$this->sequence = $sequence;
			return $this;
		}

		/**
		 * @param string $value
		 *
		 * @return FieldModuleReferenceFilter
		 */
		public function setValue ($value) {
			$this->value = $value;
			return $this;
		}

		/**
		 * @param string $valueModuleName
		 *
		 * @return FieldModuleReferenceFilter
		 */
		public function setValueModuleName ($valueModuleName) {
			$this->valueModuleName = $valueModuleName;
			return $this;
		}

		/**
		 * @param string $valueType
		 *
		 * @return FieldModuleReferenceFilter
		 */
		public function setValueType ($valueType) {
			$this->valueType = $valueType;
			return $this;
		}

		/**
		 * @param FieldModuleReferenceFilter $filter
		 */
		public function copyValuesFrom ($filter) {
			if ((empty ($filter)) || (!($filter instanceof FieldModuleReferenceFilter)) || ($this->isEqualTo ($filter))) {
				return;
			}

			$this->comparator      = $filter->getComparator ();
			$this->fieldName       = $filter->getFieldName ();
			$this->operator        = $filter->getOperator ();
			$this->sequence        = $filter->getSequence ();
			$this->value           = $filter->getValue ();
			$this->valueModuleName = $filter->getValueModuleName ();
			$this->valueType       = $filter->getValueType ();
		}

		/**
		 * @return FieldModuleReferenceFilter
		 * @throws FieldModuleReferenceFilterException
		 */
		public function duplicate () {
			$this->validate ();
			return self::getInstance ()
				->setComparator ($this->comparator)
				->setFieldName ($this->fieldName)
				->setOperator ($this->operator)
				->setSequence ($this->sequence)
				->setValue ($this->value)
				->setValueModuleName ($this->valueModuleName)
				->setValueType ($this->valueType);
		}

		/**
		 * @param FieldModuleReferenceFilter $filter
		 *
		 * @return boolean
		 */
		public function isEqualTo ($filter) {
			if (
				(empty ($filter)) ||
				(!($filter instanceof FieldModuleReferenceFilter)) ||
				($this->comparator != $filter->getComparator ()) ||
				($this->fieldName != $filter->getFieldName ()) ||
				($this->operator != $filter->getOperator ()) ||
				($this->sequence != $filter->getSequence ()) ||
				($this->value != $filter->getValue ()) ||
				($this->valueModuleName != $filter->getValueModuleName ()) ||
				($this->valueType != $filter->getValueType ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * @throws FieldModuleReferenceFilterException
		 */
		public function validate () {
			if (empty ($this->comparator)) {
				throw new FieldModuleReferenceFilterException (FieldModuleReferenceFilterException::ERROR_FIELD_MODULE_REFERENCE_FILTER_EMPTY_COMPARATOR);
			} else if (empty ($this->fieldName)) {
				throw new FieldModuleReferenceFilterException (FieldModuleReferenceFilterException::ERROR_FIELD_MODULE_REFERENCE_FILTER_EMPTY_FIELD_NAME);
			} else if (!isset ($this->sequence)) {
				throw new FieldModuleReferenceFilterException (FieldModuleReferenceFilterException::ERROR_FIELD_MODULE_REFERENCE_FILTER_EMPTY_SEQUENCE);
			} else if (!in_array ($this->valueType, self::getAvailableValueTypes ())) {
				throw new FieldModuleReferenceFilterException (FieldModuleReferenceFilterException::ERROR_FIELD_MODULE_REFERENCE_FILTER_INVALID_VALUE_TYPE);
			} else if (($this->valueType == self::TYPE_SOURCE_FIELD) && (!isset ($this->valueModuleName))) {
				throw new FieldModuleReferenceFilterException (FieldModuleReferenceFilterException::ERROR_FIELD_MODULE_REFERENCE_FILTER_EMPTY_SOURCE_FIELD_MODULE_NAME);
			} else if (($this->valueType == self::TYPE_SOURCE_FIELD) && (!isset ($this->value))) {
				throw new FieldModuleReferenceFilterException (FieldModuleReferenceFilterException::ERROR_FIELD_MODULE_REFERENCE_FILTER_EMPTY_SOURCE_FIELD_VALUE);
			}
		}

		/**
		 * @return string[]
		 */
		public static function getAvailableValueTypes () {
			return array (self::TYPE_LITERAL, self::TYPE_SOURCE_FIELD);
		}

		/**
		 * @return FieldModuleReferenceFilter
		 */
		public static function getInstance () {
			return new self ();
		}

	}
