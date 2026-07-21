<?php
	require_once ('include/platzilla/Exceptions/ReportAdvancedFilterException.php');
	require_once ('include/platzilla/Objects/Field.php');
	require_once ('include/platzilla/Objects/ReportAdvancedFilterInterface.php');

	class ReportAdvancedFilter implements ReportAdvancedFilterInterface {
		/** @var string */
		private $columnName;

		/** @var string */
		private $comparator;

		/** @var string */
		private $dataType;

		/** @var string */
		private $fieldName;

		/** @var integer */
		private $groupId;

		/** @var string */
		private $label;

		/** @var string */
		private $moduleName;

		/** @var string */
		private $operator;

		/** @var integer */
		private $reportId;

		/** @var integer */
		private $sequence;

		/** @var string */
		private $tableName;

		/** @var string */
		private $value;

		/**
		 * @param Field $field Un campo del cual copiar los valores
		 */
		public function __construct ($field = null) {
			$this->operator = '';

			if ((empty ($field)) || (!($field instanceof Field))) {
				return;
			}

			$this->columnName = $field->getColumnName ();
			$this->dataType   = $field->getDataType ();
			$this->fieldName  = $field->getName ();
			$this->label      = $field->getLabel ();
			$this->moduleName = $field->getModuleName ();
			$this->tableName  = $field->getTableName ();
		}

		/**
		 * @return string
		 */
		public function getColumnName () {
			return $this->columnName;
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
		public function getDataType () {
			return $this->dataType;
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
		public function getReportId () {
			return $this->reportId;
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
		public function getTableName () {
			return $this->tableName;
		}

		/**
		 * @return string
		 */
		public function getValue () {
			return $this->value;
		}

		/**
		 * @param string $columnName
		 *
		 * @return ReportAdvancedFilter
		 */
		public function setColumnName ($columnName) {
			$this->columnName = $columnName;
			return $this;
		}

		/**
		 * @param string $comparator
		 *
		 * @return ReportAdvancedFilter
		 */
		public function setComparator ($comparator) {
			if (in_array ($comparator, array (self::COMPARATOR_AFTER, self::COMPARATOR_BEFORE, self::COMPARATOR_BETWEEN, self::COMPARATOR_CONTAINS, self::COMPARATOR_DOES_NOT_CONTAIN, self::COMPARATOR_ENDS_WITH, self::COMPARATOR_EQUALS, self::COMPARATOR_GREATER, self::COMPARATOR_GREATER_OR_EQUALS, self::COMPARATOR_LESS, self::COMPARATOR_LESS_OR_EQUALS, self::COMPARATOR_NOT_EQUAL, self::COMPARATOR_NOT_EQUALS, self::COMPARATOR_STARTS_WITH))) {
				$this->comparator = $comparator;
			}
			return $this;
		}

		/**
		 * @param string $dataType
		 *
		 * @return ReportAdvancedFilter
		 */
		public function setDataType ($dataType) {
			if (in_array ($dataType, array (FieldInterface::DATA_TYPE_CHECKBOX, FieldInterface::DATA_TYPE_DATE, FieldInterface::DATA_TYPE_DATETIME, FieldInterface::DATA_TYPE_EMAIL, FieldInterface::DATA_TYPE_INTEGER, FieldInterface::DATA_TYPE_NEGATIVE_NUMBER, FieldInterface::DATA_TYPE_NUMBER, FieldInterface::DATA_TYPE_PASSWORD, FieldInterface::DATA_TYPE_TIME, FieldInterface::DATA_TYPE_VARCHAR))) {
				$this->dataType = $dataType;
			}
			return $this;
		}

		/**
		 * @param string $fieldName
		 *
		 * @return ReportAdvancedFilter
		 */
		public function setFieldName ($fieldName) {
			$this->fieldName = $fieldName;
			return $this;
		}

		/**
		 * @param integer $groupId
		 *
		 * @return ReportAdvancedFilter
		 */
		public function setGroupId ($groupId) {
			$this->groupId = $groupId;
			return $this;
		}

		/**
		 * @param string $label
		 *
		 * @return ReportAdvancedFilter
		 */
		public function setLabel ($label) {
			$this->label = $label;
			return $this;
		}

		/**
		 * @param string $moduleName
		 *
		 * @return ReportAdvancedFilter
		 */
		public function setModuleName ($moduleName) {
			$this->moduleName = $moduleName;
			return $this;
		}

		/**
		 * @param string $operator
		 *
		 * @return ReportAdvancedFilter
		 */
		public function setOperator ($operator) {
			if (in_array ($operator, array (self::OPERATOR_AND, self::OPERATOR_OR))) {
				$this->operator = $operator;
			}
			return $this;
		}

		/**
		 * @param integer $reportId
		 *
		 * @return ReportAdvancedFilter
		 */
		public function setReportId ($reportId) {
			$this->reportId = $reportId;
			return $this;
		}

		/**
		 * @param integer $sequence
		 *
		 * @return ReportAdvancedFilter
		 */
		public function setSequence ($sequence) {
			$this->sequence = $sequence;
			return $this;
		}

		/**
		 * @param string $tableName
		 *
		 * @return ReportAdvancedFilter
		 */
		public function setTableName ($tableName) {
			$this->tableName = $tableName;
			return $this;
		}

		/**
		 * @param string $value
		 *
		 * @return ReportAdvancedFilter
		 */
		public function setValue ($value) {
			$this->value = $value;
			return $this;
		}

		/**
		 * @param ReportAdvancedFilter $filter
		 */
		public function copyValuesFrom ($filter) {
			if ((empty ($filter)) || (!($filter instanceof ReportAdvancedFilter)) || ($this->isEqualTo ($filter))) {
				return;
			}

			$this->columnName = $filter->getColumnName ();
			$this->comparator = $filter->getComparator ();
			$this->dataType   = $filter->getDataType ();
			$this->fieldName  = $filter->getFieldName ();
			$this->label      = $filter->getLabel ();
			$this->moduleName = $filter->getModuleName ();
			$this->operator   = $filter->getOperator ();
			$this->sequence   = $filter->getSequence ();
			$this->tableName  = $filter->getTableName ();
			$this->value      = $filter->getValue ();
		}

		/**
		 * @param integer $newReportId
		 * @param integer $newGroupId
		 * @param string $oldCodeFieldName
		 * @param string $newCodeFieldName
		 *
		 * @return ReportAdvancedFilter
		 * @throws ReportAdvancedFilterException
		 */
		public function duplicate ($newReportId, $newGroupId, $oldCodeFieldName = null, $newCodeFieldName = null) {
			$this->validate ();

			if ($this->fieldName == $oldCodeFieldName) {
				$columnName = $newCodeFieldName;
				$fieldName  = $newCodeFieldName;
			} else {
				$columnName = $this->columnName;
				$fieldName  = $this->fieldName;
			}

			$object = new self ();
			return $object->setColumnName ($columnName)
				->setComparator ($this->comparator)
				->setDataType ($this->dataType)
				->setFieldName ($fieldName)
				->setGroupId ($newGroupId)
				->setLabel ($this->label)
				->setModuleName ($this->moduleName)
				->setOperator ($this->operator)
				->setReportId ($newReportId)
				->setSequence ($this->sequence)
				->setTableName ($this->tableName)
				->setValue ($this->value);
		}

		/**
		 * @param ReportAdvancedFilter $filter
		 *
		 * @return boolean
		 */
		public function isEqualTo ($filter) {
			if (
				(empty ($filter)) ||
				(!($filter instanceof ReportAdvancedFilter)) ||
				($this->columnName != $filter->getColumnName ()) ||
				($this->comparator != $filter->getComparator ()) ||
				($this->dataType != $filter->getDataType ()) ||
				($this->fieldName != $filter->getFieldName ()) ||
				($this->label != $filter->getLabel ()) ||
				($this->moduleName != $filter->getModuleName ()) ||
				($this->operator != $filter->getOperator ()) ||
				($this->sequence != $filter->getSequence ()) ||
				($this->tableName != $filter->getTableName ()) ||
				($this->value != $filter->getValue ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * @throws ReportAdvancedFilterException
		 */
		public function validate () {
			if (empty ($this->columnName)) {
				throw new ReportAdvancedFilterException (ReportAdvancedFilterException::ERROR_REPORT_ADVANCED_FILTER_EMPTY_COLUMN_NAME);
			} else if (empty ($this->comparator)) {
				throw new ReportAdvancedFilterException (ReportAdvancedFilterException::ERROR_REPORT_ADVANCED_FILTER_EMPTY_COMPARATOR);
			} else if (empty ($this->dataType)) {
				throw new ReportAdvancedFilterException (ReportAdvancedFilterException::ERROR_REPORT_ADVANCED_FILTER_EMPTY_DATA_TYPE);
			} else if (empty ($this->fieldName)) {
				throw new ReportAdvancedFilterException (ReportAdvancedFilterException::ERROR_REPORT_ADVANCED_FILTER_EMPTY_FIELD_NAME);
			} else if (empty ($this->label)) {
				throw new ReportAdvancedFilterException (ReportAdvancedFilterException::ERROR_REPORT_ADVANCED_FILTER_EMPTY_LABEL);
			} else if (!isset ($this->sequence)) {
				throw new ReportAdvancedFilterException (ReportAdvancedFilterException::ERROR_REPORT_ADVANCED_FILTER_EMPTY_SEQUENCE);
			}
		}

		/**
		 * @param Field|null $field
		 *
		 * @return ReportAdvancedFilter
		 */
		public static function getInstance ($field = null) {
			return new self ($field);
		}

	}
