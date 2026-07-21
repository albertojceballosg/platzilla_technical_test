<?php
	require_once ('include/platzilla/Exceptions/ViewColorFilterException.php');
	require_once ('include/platzilla/Objects/Field.php');
	require_once ('include/platzilla/Objects/ViewColorFilterInterface.php');

	/**
	 * Class ViewColorFilter
	 *
	 * Esta clase define el objeto ""Vista Filtro Color" el cual hace referencia a las vista que controla el color en el aspecto visual
	 * de los registros consultados a través de los filtros, en la "Plataforma" y/o "Instancia". La clase está asociada al objeto "Campo"
	 */
	class ViewColorFilter implements ViewColorFilterInterface {
		/** @var string */
		private $columnName;

		/** @var string */
		private $comparator;

		/** @var string */
		private $dataType;

		/** @var DateTime */
		private $endDate;

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
		private $sequence;

		/** @var DateTime */
		private $startDate;

		/** @var string */
		private $tableName;

		/** @var string */
		private $value;

		/** @var integer */
		private $viewId;

		/**
		 * ViewColorFilter constructor.
		 *
		 * @param null $field
		 * @param array|null $dummyData
		 */
		public function __construct ($field = null, $dummyData = null) {
			$this->operator = '';
			$moduleName     = '';
			$labelField     = '';
			if (((empty ($field)) || (!($field instanceof Field))) && empty ($dummyData)) {
				return;
			} else if (!empty($dummyData)) {
				$dummy = explode ('@', $dummyData [3], 2);
				$moduleName = $dummy [0];
				$labelField = $dummy [1];
			}

			$this->columnName = (!empty ($field)) ? $field->getColumnName () : $dummyData [1];
			$this->dataType   = (!empty ($field)) ? $field->getDataType () : $dummyData [4];
			$this->fieldName  = (!empty ($field)) ? $field->getName () : $dummyData [2];
			$this->label      = (!empty ($field)) ? $field->getLabel () : $labelField;
			$this->moduleName = (!empty ($field)) ? $field->getModuleName () : $moduleName;
			$this->tableName  = (!empty ($field)) ? $field->getTableName () : $dummyData [0];
		}

		/**
		 * Para obtener el nombre de la columna para el filtro de color
		 *
		 * @return string
		 */
		public function getColumnName () {
			return $this->columnName;
		}

		/**
		 * Para obtener la operacion (igual, mayor que, menor que) se empleara en la comparacion condicionara el filtro de color
		 *
		 * @return string
		 */
		public function getComparator () {
			return $this->comparator;
		}

		/**
		 * Para obtener el tipo de dato se empleara en la comparacion condicionara el filtro de color
		 *
		 * @return string
		 */
		public function getDataType () {
			return $this->dataType;
		}

		/**
		 * @return DateTime
		 */
		public function getEndDate () {
			return $this->endDate;
		}

		/**
		 * Para obtener el nombre del campo se empleara en la comparacion condicionara el filtro de color
		 *
		 * @return string
		 */
		public function getFieldName () {
			return $this->fieldName;
		}

		/**
		 * Para obtener el ID del grupo del filtro de color
		 *
		 * @return integer
		 */
		public function getGroupId () {
			return $this->groupId;
		}

		/**
		 * Para obtener la etiqueta del filtro de color
		 *
		 * @return string
		 */
		public function getLabel () {
			return $this->label;
		}

		/**
		 * Para obtener el nombre del modulo que se utilizara para el filtro de color
		 *
		 * @return string
		 */
		public function getModuleName () {
			return $this->moduleName;
		}

		/**
		 * Para obtener el operador se empleara para el filtro
		 *
		 * @return string
		 */
		public function getOperator () {
			return $this->operator;
		}

		/**
		 * Para obtener la secuencia del filtro de color
		 *
		 * @return integer
		 */
		public function getSequence () {
			return $this->sequence;
		}

		/**
		 * @return DateTime
		 */
		public function getStartDate () {
			return $this->startDate;
		}

		/**
		 * Para obtener el nombre de la tabla donde se almacenara el filtro de color
		 *
		 * @return string
		 */
		public function getTableName () {
			return $this->tableName;
		}

		/**
		 * Para obtener el id de la vista donde se configurara el filtro de color
		 *
		 * @return integer
		 */
		public function getViewId () {
			return $this->viewId;
		}

		/**
		 * Para obtener el valor del filtro de color
		 *
		 * @return string
		 */
		public function getValue () {
			return $this->value;
		}

		/**
		 * Establece el nombre de la columna para el filtro de color
		 *
		 * @param string $columnName
		 *
		 * @return ViewColorFilter
		 */
		public function setColumnName ($columnName) {
			$this->columnName = $columnName;
			return $this;
		}

		/**
		 * Establece la operacion (igual, mayor que, menor que) se empleara en la comparacion condicionara el filtro de color
		 *
		 * @param string $comparator
		 *
		 * @return ViewColorFilter
		 */
		public function setComparator ($comparator) {
			if (
				in_array (
					$comparator, array (
						self::COMPARATOR_AFTER,
						self::COMPARATOR_BEFORE,
						self::COMPARATOR_BETWEEN,
						self::COMPARATOR_CONTAINS,
						self::COMPARATOR_DOES_NOT_CONTAIN,
						self::COMPARATOR_ENDS_WITH,
						self::COMPARATOR_EQUALS,
						self::COMPARATOR_GREATER,
						self::COMPARATOR_GREATER_OR_EQUALS,
						self::COMPARATOR_LESS,
						self::COMPARATOR_LESS_OR_EQUALS,
						self::COMPARATOR_NOT_EQUALS,
						self::COMPARATOR_STARTS_WITH,
						self::PERIOD_CURRENT_MONTH,
						self::PERIOD_CURRENT_QUARTER,
						self::PERIOD_CURRENT_WEEK,
						self::PERIOD_CURRENT_YEAR,
						self::PERIOD_CUSTOM,
						self::PERIOD_LAST_MONTH,
						self::PERIOD_LAST_7_DAYS,
						self::PERIOD_LAST_30_DAYS,
						self::PERIOD_LAST_60_DAYS,
						self::PERIOD_LAST_90_DAYS,
						self::PERIOD_LAST_120_DAYS,
						self::PERIOD_NEXT_7_DAYS,
						self::PERIOD_NEXT_30_DAYS,
						self::PERIOD_NEXT_60_DAYS,
						self::PERIOD_NEXT_90_DAYS,
						self::PERIOD_NEXT_120_DAYS,
						self::PERIOD_LAST_WEEK,
						self::PERIOD_NEXT_MONTH,
						self::PERIOD_NEXT_QUARTER,
						self::PERIOD_NEXT_WEEK,
						self::PERIOD_NEXT_YEAR,
						self::PERIOD_PREVIOUS_QUARTER,
						self::PERIOD_PREVIOUS_YEAR,
						self::PERIOD_TODAY,
						self::PERIOD_TOMORROW,
						self::PERIOD_YESTERDAY,
					)
				)
			) {
				$this->comparator = $comparator;
			}
			return $this;
		}

		/**
		 * Establece el tipo de dato se empleara en la comparacion condicionara el filtro de color
		 *
		 * @param string $dataType
		 *
		 * @return ViewColorFilter
		 */
		public function setDataType ($dataType) {
			if (in_array ($dataType, array (FieldInterface::DATA_TYPE_CHECKBOX, FieldInterface::DATA_TYPE_DATE, FieldInterface::DATA_TYPE_DATETIME, FieldInterface::DATA_TYPE_EMAIL, FieldInterface::DATA_TYPE_INTEGER, FieldInterface::DATA_TYPE_NEGATIVE_NUMBER, FieldInterface::DATA_TYPE_NUMBER, FieldInterface::DATA_TYPE_PASSWORD, FieldInterface::DATA_TYPE_TIME, FieldInterface::DATA_TYPE_VARCHAR))) {
				$this->dataType = $dataType;
			}
			return $this;
		}

		/**
		 * @param DateTime|string $endDate
		 *
		 * @return ViewColorFilter
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
		 * Establece el nombre del campo se empleara en la comparacion condicionara el filtro de color
		 *
		 * @param string $fieldName
		 *
		 * @return ViewColorFilter
		 */
		public function setFieldName ($fieldName) {
			$this->fieldName = $fieldName;
			return $this;
		}

		/**
		 * Establece el ID del grupo del filtro de color
		 *
		 * @param integer $groupId
		 *
		 * @return ViewColorFilter
		 */
		public function setGroupId ($groupId) {
			$this->groupId = $groupId;
			return $this;
		}

		/**
		 * Establece la etiqueta del filtro de color
		 *
		 * @param string $label
		 *
		 * @return ViewColorFilter
		 */
		public function setLabel ($label) {
			$this->label = $label;
			return $this;
		}

		/**
		 * Establece el nombre del modulo que se utilizara para el filtro de color
		 *
		 * @param string $moduleName
		 *
		 * @return ViewColorFilter
		 */
		public function setModuleName ($moduleName) {
			$this->moduleName = $moduleName;
			return $this;
		}

		/**
		 * Establece el operador se empleara para el filtro
		 *
		 * @param string $operator
		 *
		 * @return ViewColorFilter
		 */
		public function setOperator ($operator) {
			if (in_array ($operator, array (self::OPERATOR_AND, self::OPERATOR_OR))) {
				$this->operator = $operator;
			}
			return $this;
		}

		/**
		 * Establece la secuencia del filtro de color
		 *
		 * @param integer $sequence
		 *
		 * @return ViewColorFilter
		 */
		public function setSequence ($sequence) {
			$this->sequence = $sequence;
			return $this;
		}

		/**
		 * @param DateTime|string $startDate
		 *
		 * @return ViewColorFilter
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
		 * Establece el nombre de la tabla donde se almacenara el filtro de color
		 *
		 * @param string $tableName
		 *
		 * @return ViewColorFilter
		 */
		public function setTableName ($tableName) {
			$this->tableName = $tableName;
			return $this;
		}

		/**
		 * Establece el valor del filtro de color
		 *
		 * @param string $value
		 *
		 * @return ViewColorFilter
		 */
		public function setValue ($value) {
			$this->value = $value;
			return $this;
		}

		/**
		 * Establece el id de la vista donde se configurara el filtro de color
		 *
		 * @param integer $viewId
		 *
		 * @return ViewColorFilter
		 */
		public function setViewId ($viewId) {
			$this->viewId = $viewId;
			return $this;
		}

		/**
		 * Para realizar copia de los valores/atributos del filtro de color
		 *
		 * @param ViewColorFilter $filter
		 */
		public function copyValuesFrom ($filter) {
			if ((empty ($filter)) || (!($filter instanceof ViewColorFilter)) || ($this->isEqualTo ($filter))) {
				return;
			}

			$this->columnName = $filter->getColumnName ();
			$this->comparator = $filter->getComparator ();
			$this->dataType   = $filter->getDataType ();
			$this->endDate    = $filter->getEndDate ();
			$this->fieldName  = $filter->getFieldName ();
			$this->label      = $filter->getLabel ();
			$this->moduleName = $filter->getModuleName ();
			$this->operator   = $filter->getOperator ();
			$this->sequence   = $filter->getSequence ();
			$this->startDate  = $filter->getStartDate ();
			$this->tableName  = $filter->getTableName ();
			$this->value      = $filter->getValue ();
		}

		/**
		 * Para duplicar los valores/atributos del filtro de color
		 *
		 * @param integer $newViewId
		 * @param integer $newGroupId
		 * @param string $oldCodeFieldName
		 * @param string $newCodeFieldName
		 *
		 * @return ViewColorFilter
		 * @throws ViewColorFilterException
		 */
		public function duplicate ($newViewId, $newGroupId, $oldCodeFieldName = null, $newCodeFieldName = null) {
			$this->validate ();

			if ($this->fieldName == $oldCodeFieldName) {
				$columnName = $newCodeFieldName;
				$fieldName = $newCodeFieldName;
			} else {
				$columnName = $this->columnName;
				$fieldName  = $this->fieldName;
			}

			$object = new self ();
			return $object->setColumnName ($columnName)
				->setComparator ($this->comparator)
				->setDataType ($this->dataType)
				->setEndDate ($this->endDate)
				->setFieldName ($fieldName)
				->setGroupId ($newGroupId)
				->setLabel ($this->label)
				->setModuleName ($this->moduleName)
				->setOperator ($this->operator)
				->setSequence ($this->sequence)
				->setStartDate ($this->startDate)
				->setTableName ($this->tableName)
				->setValue ($this->value)
				->setViewId ($newViewId);
		}

		/**
		 * Para comparar si dos filtros de color son iguales entre si
		 *
		 * @param ViewColorFilter $filter
		 *
		 * @return boolean
		 */
		public function isEqualTo ($filter) {
			if (
				(empty ($filter)) ||
				(!($filter instanceof ViewColorFilter)) ||
				($this->columnName != $filter->getColumnName ()) ||
				($this->comparator != $filter->getComparator ()) ||
				($this->dataType != $filter->getDataType ()) ||
				($this->endDate != $filter->getEndDate ()) ||
				($this->fieldName != $filter->getFieldName ()) ||
				($this->label != $filter->getLabel ()) ||
				($this->moduleName != $filter->getModuleName ()) ||
				($this->operator != $filter->getOperator ()) ||
				($this->sequence != $filter->getSequence ()) ||
				($this->startDate != $filter->getStartDate ()) ||
				($this->tableName != $filter->getTableName ()) ||
				($this->value != $filter->getValue ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Valida que los atributos/valores (columna, operador, tipo de dato, nombre del campo, etiqueta, secuencia) de la vista esten correctos
		 *
		 * @throws ViewColorFilterException
		 */
		public function validate () {
			if (empty ($this->columnName)) {
				throw new ViewColorFilterException (ViewColorFilterException::ERROR_VIEW_COLOR_FILTER_EMPTY_COLUMN_NAME);
			} else if (empty ($this->comparator)) {
				throw new ViewColorFilterException (ViewColorFilterException::ERROR_VIEW_COLOR_FILTER_EMPTY_COMPARATOR);
			} else if (empty ($this->dataType)) {
				throw new ViewColorFilterException (ViewColorFilterException::ERROR_VIEW_COLOR_FILTER_EMPTY_DATA_TYPE);
			} else if (empty ($this->fieldName)) {
				throw new ViewColorFilterException (ViewColorFilterException::ERROR_VIEW_COLOR_FILTER_EMPTY_FIELD_NAME);
			} else if (empty ($this->label)) {
				throw new ViewColorFilterException (ViewColorFilterException::ERROR_VIEW_COLOR_FILTER_EMPTY_LABEL);
			} else if (!isset ($this->sequence)) {
				throw new ViewColorFilterException (ViewColorFilterException::ERROR_VIEW_COLOR_FILTER_EMPTY_SEQUENCE);
			} else if ($this->comparator == self::PERIOD_CUSTOM) {
				if (empty ($this->startDate)) {
					throw new ViewColorFilterException (ViewColorFilterException::ERROR_VIEW_COLOR_FILTER_EMPTY_START_DATE);
				} else if (empty ($this->endDate)) {
					throw new ViewColorFilterException (ViewColorFilterException::ERROR_VIEW_COLOR_FILTER_EMPTY_END_DATE);
				}
			}
		}

		/**
		 * Instanciación de la clase ViewColorFilter. Se obtiene un objeto ViewColorFilter con los valores de la clase
		 *
		 * @param Field|null $field
		 * @param array|null $dummyData
		 *
		 * @return ViewColorFilter
		 */
		public static function getInstance ($field = null, $dummyData = null) {
			return new self ($field, $dummyData);
		}

	}
