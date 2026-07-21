<?php
	require_once ('include/platzilla/Exceptions/ReportColumnException.php');
	require_once ('include/platzilla/Objects/Field.php');
	require_once ('include/platzilla/Objects/ReportColumnInterface.php');

	class ReportColumn implements ReportColumnInterface {
		/** @var string */
		private $columnName;

		/** @var string */
		private $dataType;

		/** @var string */
		private $fieldName;

		/** @var string */
		private $label;

		/** @var string */
		private $moduleName;

		/** @var integer */
		private $reportId;

		/** @var integer */
		private $sequence;

		/** @var string */
		private $sortOrder;

		/** @var string */
		private $tableName;

		/** @var string */
		private $totalsOperation;

		/**
		 * @param Field $field Un campo del cual copiar los valores
		 */
		public function __construct ($field = null) {
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
		public function getSortOrder () {
			return $this->sortOrder;
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
		public function getTotalsOperation () {
			return $this->totalsOperation;
		}

		/**
		 * @param string $columnName
		 *
		 * @return ReportColumn
		 */
		public function setColumnName ($columnName) {
			$this->columnName = $columnName;
			return $this;
		}

		/**
		 * @param string $dataType
		 *
		 * @return ReportColumn
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
		 * @return ReportColumn
		 */
		public function setFieldName ($fieldName) {
			$this->fieldName = $fieldName;
			return $this;
		}

		/**
		 * @param string $label
		 *
		 * @return ReportColumn
		 */
		public function setLabel ($label) {
			$this->label = $label;
			return $this;
		}

		/**
		 * @param string $moduleName
		 *
		 * @return ReportColumn
		 */
		public function setModuleName ($moduleName) {
			$this->moduleName = $moduleName;
			return $this;
		}

		/**
		 * @param integer $reportId
		 *
		 * @return ReportColumn
		 */
		public function setReportId ($reportId) {
			$this->reportId = $reportId;
			return $this;
		}

		/**
		 * @param integer $sequence
		 *
		 * @return ReportColumn
		 */
		public function setSequence ($sequence) {
			$this->sequence = $sequence;
			return $this;
		}

		/**
		 * @param string $sortOrder
		 *
		 * @return ReportColumn
		 */
		public function setSortOrder ($sortOrder) {
			if (($sortOrder === null) || (in_array ($sortOrder, array (self::SORT_ORDER_ASCENDING, self::SORT_ORDER_DESCENDING)))) {
				$this->sortOrder = $sortOrder;
			}
			return $this;
		}

		/**
		 * @param string $tableName
		 *
		 * @return ReportColumn
		 */
		public function setTableName ($tableName) {
			$this->tableName = $tableName;
			return $this;
		}

		/**
		 * @param string $totalsOperation
		 *
		 * @return ReportColumn
		 */
		public function setTotalsOperation ($totalsOperation) {
			if (($totalsOperation === null) || (in_array ($totalsOperation, array (self::TOTALS_OPERATION_AVERAGE, self::TOTALS_OPERATION_MAXIMUM, self::TOTALS_OPERATION_MINIMUM, self::TOTALS_OPERATION_SUM)))) {
				$this->totalsOperation = $totalsOperation;
			}
			return $this;
		}

		/**
		 * @param ReportColumn $column
		 */
		public function copyValuesFrom ($column) {
			if ((empty ($column)) || (!($column instanceof ReportColumn)) || ($this->isEqualTo ($column))) {
				return;
			}

			$this->columnName      = $column->getColumnName ();
			$this->dataType        = $column->getDataType ();
			$this->fieldName       = $column->getFieldName ();
			$this->label           = $column->getLabel ();
			$this->moduleName      = $column->getModuleName ();
			$this->sequence        = $column->getSequence ();
			$this->sortOrder       = $column->getSortOrder ();
			$this->tableName       = $column->getTableName ();
			$this->totalsOperation = $column->getTotalsOperation ();
		}

		/**
		 * @param integer $newReportId
		 * @param string $oldCodeFieldName
		 * @param string $newCodeFieldName
		 *
		 * @return ReportColumn
		 * @throws ReportColumnException
		 */
		public function duplicate ($newReportId, $oldCodeFieldName = null, $newCodeFieldName = null) {
			$this->validate ();

			if ($this->fieldName == $oldCodeFieldName) {
				$columnName = $newCodeFieldName;
				$fieldName = $newCodeFieldName;
			} else {
				$columnName = $this->columnName;
				$fieldName = $this->fieldName;
			}

			$object = new self ();
			return $object->setColumnName ($columnName)
				->setDataType ($this->dataType)
				->setFieldName ($fieldName)
				->setLabel ($this->label)
				->setModuleName ($this->moduleName)
				->setReportId ($newReportId)
				->setSequence ($this->sequence)
				->setSortOrder ($this->sortOrder)
				->setTableName ($this->tableName)
				->setTotalsOperation ($this->totalsOperation);
		}

		/**
		 * @param ReportColumn $column
		 *
		 * @return boolean
		 */
		public function isEqualTo ($column) {
			if (
				(empty ($column)) ||
				(!($column instanceof ReportColumn)) ||
				($this->columnName != $column->getColumnName ()) ||
				($this->dataType != $column->getDataType ()) ||
				($this->fieldName != $column->getFieldName ()) ||
				($this->label != $column->getLabel ()) ||
				($this->moduleName != $column->getModuleName ()) ||
				($this->sequence != $column->getSequence ()) ||
				($this->sortOrder != $column->getSortOrder ()) ||
				($this->tableName != $column->getTableName ()) ||
				($this->totalsOperation != $column->getTotalsOperation ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * @throws ReportColumnException
		 */
		public function validate () {
			if (empty ($this->columnName)) {
				throw new ReportColumnException (ReportColumnException::ERROR_REPORT_COLUMN_EMPTY_COLUMN_NAME);
			} else if (empty ($this->fieldName)) {
				throw new ReportColumnException (ReportColumnException::ERROR_REPORT_COLUMN_EMPTY_FIELD_NAME);
			} else if (empty ($this->label)) {
				throw new ReportColumnException (ReportColumnException::ERROR_REPORT_COLUMN_EMPTY_LABEL);
			} else if (!isset ($this->sequence)) {
				throw new ReportColumnException (ReportColumnException::ERROR_REPORT_COLUMN_EMPTY_SEQUENCE);
			}
		}

		/**
		 * @param Field $field
		 *
		 * @return ReportColumn
		 */
		public static function getInstance ($field = null) {
			return new self ($field);
		}

	}
