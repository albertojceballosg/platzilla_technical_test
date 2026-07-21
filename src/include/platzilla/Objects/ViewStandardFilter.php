<?php
	require_once ('include/platzilla/Exceptions/ViewStandardFilterException.php');
	require_once ('include/platzilla/Objects/Field.php');
	require_once ('include/platzilla/Objects/ViewStandardFilterInterface.php');

	class ViewStandardFilter implements ViewStandardFilterInterface {
		/** @var string */
		private $columnName;

		/** @var DateTime */
		private $endDate;

		/** @var string */
		private $fieldName;

		/** @var string */
		private $label;

		/** @var string */
		private $moduleName;

		/** @var string */
		private $period;

		/** @var DateTime */
		private $startDate;

		/** @var string */
		private $tableName;

		/** @var integer */
		private $viewId;

		/**
		 * @param Field $field Un campo del cual copiar los valores
		 */
		public function __construct ($field = null) {
			if ((empty ($field)) || (!($field instanceof Field))) {
				return;
			}

			$this->columnName = $field->getColumnName ();
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
		 * @return DateTime
		 */
		public function getEndDate () {
			return $this->endDate;
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
		 * @return string
		 */
		public function getPeriod () {
			return $this->period;
		}

		/**
		 * @return DateTime
		 */
		public function getStartDate () {
			return $this->startDate;
		}

		/**
		 * @return string
		 */
		public function getTableName () {
			return $this->tableName;
		}

		/**
		 * @return integer
		 */
		public function getViewId () {
			return $this->viewId;
		}

		/**
		 * @param string $columnName
		 *
		 * @return ViewStandardFilter
		 */
		public function setColumnName ($columnName) {
			$this->columnName = $columnName;
			return $this;
		}

		/**
		 * @param DateTime|string $endDate
		 *
		 * @return ViewStandardFilter
		 */
		public function setEndDate ($endDate) {
			if (empty ($endDate)) {
				$this->endDate = null;
			} else if ($endDate instanceof DateTime) {
				$this->endDate = $endDate->setTime (0, 0, 0);
			} else {
				$dummy = DateTime::createFromFormat ('Y-m-d', $endDate)->setTime (0, 0, 0);
				if (($dummy) && ($dummy->format ('Y-m-d') === $endDate)) {
					$this->endDate = $dummy;
				}
			}
			return $this;
		}

		/**
		 * @param string $fieldName
		 *
		 * @return ViewStandardFilter
		 */
		public function setFieldName ($fieldName) {
			$this->fieldName = $fieldName;
			return $this;
		}

		/**
		 * @param string $label
		 *
		 * @return ViewStandardFilter
		 */
		public function setLabel ($label) {
			$this->label = $label;
			return $this;
		}

		/**
		 * @param string $moduleName
		 *
		 * @return ViewStandardFilter
		 */
		public function setModuleName ($moduleName) {
			$this->moduleName = $moduleName;
			return $this;
		}

		/**
		 * @param string $period
		 *
		 * @return ViewStandardFilter
		 */
		public function setPeriod ($period) {
			if (in_array ($period, array (self::PERIOD_CURRENT_MONTH, self::PERIOD_CURRENT_QUARTER, self::PERIOD_CURRENT_WEEK, self::PERIOD_CURRENT_YEAR, self::PERIOD_CUSTOM, self::PERIOD_LAST_MONTH, self::PERIOD_LAST_7_DAYS, self::PERIOD_LAST_30_DAYS, self::PERIOD_LAST_60_DAYS, self::PERIOD_LAST_90_DAYS, self::PERIOD_LAST_120_DAYS, self::PERIOD_NEXT_7_DAYS, self::PERIOD_NEXT_30_DAYS, self::PERIOD_NEXT_60_DAYS, self::PERIOD_NEXT_90_DAYS, self::PERIOD_NEXT_120_DAYS, self::PERIOD_LAST_WEEK, self::PERIOD_NEXT_MONTH, self::PERIOD_NEXT_QUARTER, self::PERIOD_NEXT_WEEK, self::PERIOD_NEXT_YEAR, self::PERIOD_PREVIOUS_QUARTER, self::PERIOD_PREVIOUS_YEAR, self::PERIOD_TODAY, self::PERIOD_TOMORROW, self::PERIOD_YESTERDAY))) {
				$this->period = $period;
			}
			return $this;
		}

		/**
		 * @param DateTime|string $startDate
		 *
		 * @return ViewStandardFilter
		 */
		public function setStartDate ($startDate) {
			if (empty ($startDate)) {
				$this->startDate = null;
			} else if ($startDate instanceof DateTime) {
				$this->startDate = $startDate->setTime (0, 0, 0);
			} else {
				$dummy = DateTime::createFromFormat ('Y-m-d', $startDate)->setTime (0, 0, 0);
				if (($dummy) && ($dummy->format ('Y-m-d') === $startDate)) {
					$this->startDate = $dummy;
				}
			}
			return $this;
		}

		/**
		 * @param string $tableName
		 *
		 * @return ViewStandardFilter
		 */
		public function setTableName ($tableName) {
			$this->tableName = $tableName;
			return $this;
		}

		/**
		 * @param integer $viewId
		 *
		 * @return ViewStandardFilter
		 */
		public function setViewId ($viewId) {
			$this->viewId = $viewId;
			return $this;
		}

		/**
		 * @param ViewStandardFilter $filter
		 */
		public function copyValuesFrom ($filter) {
			if ((empty ($filter)) || (!($filter instanceof ViewStandardFilter))) {
				return;
			}

			$this->columnName = $filter->getColumnName ();
			$this->endDate    = $filter->getEndDate ();
			$this->fieldName  = $filter->getFieldName ();
			$this->label      = $filter->getLabel ();
			$this->moduleName = $filter->getModuleName ();
			$this->period     = $filter->getPeriod ();
			$this->startDate  = $filter->getStartDate ();
			$this->tableName  = $filter->getTableName ();
		}

		/**
		 * @param integer $newViewId
		 *
		 * @return ViewStandardFilter
		 */
		public function duplicate ($newViewId) {
			$this->validate ();
			return self::getInstance ()
				->setColumnName ($this->getColumnName ())
				->setEndDate ($this->getEndDate ())
				->setFieldName ($this->getFieldName ())
				->setLabel ($this->getLabel ())
				->setModuleName ($this->getModuleName ())
				->setPeriod ($this->getPeriod ())
				->setStartDate ($this->getStartDate ())
				->setTableName ($this->getTableName ())
				->setViewId ($newViewId);
		}

		/**
		 * @param ViewStandardFilter $filter
		 *
		 * @return boolean
		 */
		public function isEqualTo ($filter) {
			if (
				(empty ($filter)) ||
				(!($filter instanceof ViewStandardFilter)) ||
				($this->columnName != $filter->getColumnName ()) ||
				($this->endDate != $filter->getEndDate ()) ||
				($this->fieldName != $filter->getFieldName ()) ||
				($this->label != $filter->getLabel ()) ||
				($this->moduleName != $filter->getModuleName ()) ||
				($this->period != $filter->getPeriod ()) ||
				($this->startDate != $filter->getStartDate ()) ||
				($this->tableName != $filter->getTableName ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * @throws ViewStandardFilterException
		 */
		public function validate () {
			if (empty ($this->columnName)) {
				throw new ViewStandardFilterException (ViewStandardFilterException::ERROR_VIEW_STANDARD_FILTER_EMPTY_COLUMN_NAME);
			} else if (empty ($this->fieldName)) {
				throw new ViewStandardFilterException (ViewStandardFilterException::ERROR_VIEW_STANDARD_FILTER_EMPTY_FIELD_NAME);
			} else if (empty ($this->label)) {
				throw new ViewStandardFilterException (ViewStandardFilterException::ERROR_VIEW_STANDARD_FILTER_EMPTY_LABEL);
			} else if (empty ($this->period)) {
				throw new ViewStandardFilterException (ViewStandardFilterException::ERROR_VIEW_STANDARD_FILTER_EMPTY_PERIOD);
			} else if ($this->period == self::PERIOD_CUSTOM) {
				if (empty ($this->startDate)) {
					throw new ViewStandardFilterException (ViewStandardFilterException::ERROR_VIEW_STANDARD_FILTER_EMPTY_START_DATE);
				} else if (empty ($this->endDate)) {
					throw new ViewStandardFilterException (ViewStandardFilterException::ERROR_VIEW_STANDARD_FILTER_EMPTY_END_DATE);
				}
			}
		}

		/**
		 * @param Field|null $field
		 *
		 * @return ViewStandardFilter
		 */
		public static function getInstance ($field = null) {
			return new self ($field);
		}

	}
