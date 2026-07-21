<?php
	require_once ('include/platzilla/Exceptions/ViewAdvancedFilterException.php');
	require_once ('include/platzilla/Objects/Field.php');
	require_once ('include/platzilla/Objects/ViewAdvancedFilterInterface.php');

	/**
	 * Class ViewAdvancedFilter
	 *
	 * Esta clase define el objeto "Vista Filtro Avanzado" el cual hace referencia a las vista que controla el aspecto visual
	 * de la lista de registros consultados a través de los filtros avanzados, en la "Plataforma" y/o "Instancia". La clase está asociada al objeto "Campo".
	 */
	class ViewAdvancedFilter implements ViewAdvancedFilterInterface {
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
		private $sequence;

		/** @var string */
		private $tableName;

		/** @var string */
		private $value;

		/** @var integer */
		private $viewId;

		/**
		 * ViewAdvancedFilter constructor.
		 *
		 * @param null $field Un campo del cual copiar los valores
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
		 * Para obtener el nombre de la columna para el filtro
		 *
		 * @return string
		 */
		public function getColumnName () {
			return $this->columnName;
		}

		/**
		 * Para obtener la operacion (igual, mayor que, menor que) se empleara en la comparacion condicionara el filtro
		 *
		 * @return string
		 */
		public function getComparator () {
			return $this->comparator;
		}

		/**
		 * Para obtener el tipo de dato se empleara en la comparacion condicionara el filtro
		 *
		 * @return string
		 */
		public function getDataType () {
			return $this->dataType;
		}

		/**
		 * Para obtener el nombre del campo se empleara en la comparacion condicionara el filtro
		 *
		 * @return string
		 */
		public function getFieldName () {
			return $this->fieldName;
		}

		/**
		 * Para obtener el ID del grupo del filtro
		 *
		 * @return integer
		 */
		public function getGroupId () {
			return $this->groupId;
		}

		/**
		 * Para obtener la etiqueta del filtro
		 *
		 * @return string
		 */
		public function getLabel () {
			return $this->label;
		}

		/**
		 * Para obtener el nombre del modulo que se utilizara para el filtro
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
		 * Para obtener la secuencia del filtro
		 *
		 * @return integer
		 */
		public function getSequence () {
			return $this->sequence;
		}

		/**
		 * Para obtener el nombre de la tabla donde se almacenara el filtro
		 *
		 * @return string
		 */
		public function getTableName () {
			return $this->tableName;
		}

		/**
		 * Para obtener el id de la vista donde se configurara el filtro
		 *
		 * @return integer
		 */
		public function getViewId () {
			return $this->viewId;
		}

		/**
		 * Para obtener el valor del filtro
		 *
		 * @return string
		 */
		public function getValue () {
			return $this->value;
		}

		/**
		 * Establece el nombre de la columna que se configurara para el filtro
		 *
		 * @param string $columnName
		 *
		 * @return ViewAdvancedFilter
		 */
		public function setColumnName ($columnName) {
			$this->columnName = $columnName;
			return $this;
		}

		/**
		 * Establece la operacion (igual, mayor que, menor que) se empleara en la comparacion condicionara el filtro
		 *
		 * @param string $comparator
		 *
		 * @return ViewAdvancedFilter
		 */
		public function setComparator ($comparator) {
			if (in_array ($comparator, array (self::COMPARATOR_AFTER, self::COMPARATOR_BEFORE, self::COMPARATOR_BETWEEN, self::COMPARATOR_CONTAINS, self::COMPARATOR_DOES_NOT_CONTAIN, self::COMPARATOR_ENDS_WITH, self::COMPARATOR_EQUALS, self::COMPARATOR_GREATER, self::COMPARATOR_GREATER_OR_EQUALS, self::COMPARATOR_LESS, self::COMPARATOR_LESS_OR_EQUALS, self::COMPARATOR_NOT_EQUALS, self::COMPARATOR_STARTS_WITH))) {
				$this->comparator = $comparator;
			}
			return $this;
		}

		/**
		 * Establece el tipo de dato se empleara en la comparacion condicionara el filtro
		 *
		 * @param string $dataType
		 *
		 * @return ViewAdvancedFilter
		 */
		public function setDataType ($dataType) {
			if (in_array ($dataType, array (FieldInterface::DATA_TYPE_CHECKBOX, FieldInterface::DATA_TYPE_DATE, FieldInterface::DATA_TYPE_DATETIME, FieldInterface::DATA_TYPE_EMAIL, FieldInterface::DATA_TYPE_INTEGER, FieldInterface::DATA_TYPE_NEGATIVE_NUMBER, FieldInterface::DATA_TYPE_NUMBER, FieldInterface::DATA_TYPE_PASSWORD, FieldInterface::DATA_TYPE_TIME, FieldInterface::DATA_TYPE_VARCHAR))) {
				$this->dataType = $dataType;
			}
			return $this;
		}

		/**
		 * Establece el nombre del campo se empleara en la comparacion condicionara el filtro
		 *
		 * @param string $fieldName
		 *
		 * @return ViewAdvancedFilter
		 */
		public function setFieldName ($fieldName) {
			$this->fieldName = $fieldName;
			return $this;
		}

		/**
		 * Establece el ID del grupo del filtro
		 *
		 * @param integer $groupId
		 *
		 * @return ViewAdvancedFilter
		 */
		public function setGroupId ($groupId) {
			$this->groupId = $groupId;
			return $this;
		}

		/**
		 * Estable la etiqueta del filtro
		 *
		 * @param string $label
		 *
		 * @return ViewAdvancedFilter
		 */
		public function setLabel ($label) {
			$this->label = $label;
			return $this;
		}

		/**
		 * Establece el nombre del modulo que se utilizara para el filtro
		 *
		 * @param string $moduleName
		 *
		 * @return ViewAdvancedFilter
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
		 * @return ViewAdvancedFilter
		 */
		public function setOperator ($operator) {
			if (in_array ($operator, array (self::OPERATOR_AND, self::OPERATOR_OR))) {
				$this->operator = $operator;
			}
			return $this;
		}

		/**
		 * Establece la secuencia del filtro
		 *
		 * @param integer $sequence
		 *
		 * @return ViewAdvancedFilter
		 */
		public function setSequence ($sequence) {
			$this->sequence = $sequence;
			return $this;
		}

		/**
		 * Establece el nombre de la tabla donde se almacenara el filtro
		 *
		 * @param string $tableName
		 *
		 * @return ViewAdvancedFilter
		 */
		public function setTableName ($tableName) {
			$this->tableName = $tableName;
			return $this;
		}

		/**
		 * Establece el valor del filtro
		 *
		 * @param string $value
		 *
		 * @return ViewAdvancedFilter
		 */
		public function setValue ($value) {
			$this->value = $value;
			return $this;
		}

		/**
		 * Establece el id de la vista donde se configurara el filtro
		 *
		 * @param integer $viewId
		 *
		 * @return ViewAdvancedFilter
		 */
		public function setViewId ($viewId) {
			$this->viewId = $viewId;
			return $this;
		}

		/**
		 * Para realizar copia de los valores/atributos del filtro
		 *
		 * @param ViewAdvancedFilter $filter
		 */
		public function copyValuesFrom ($filter) {
			if ((empty ($filter)) || (!($filter instanceof ViewAdvancedFilter)) || ($this->isEqualTo ($filter))) {
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
		 * Para duplicar los valores/atributos del filtro avanzado
		 *
		 * @param integer $newViewId
		 * @param integer $newGroupId
		 * @param string $oldCodeFieldName
		 * @param string $newCodeFieldName
		 *
		 * @return ViewAdvancedFilter
		 * @throws ViewAdvancedFilterException
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
				->setFieldName ($fieldName)
				->setGroupId ($newGroupId)
				->setLabel ($this->label)
				->setModuleName ($this->moduleName)
				->setOperator ($this->operator)
				->setSequence ($this->sequence)
				->setTableName ($this->tableName)
				->setValue ($this->value)
				->setViewId ($newViewId);
		}

		/**
		 * Para comparar si dos filtros son iguales entre si
		 *
		 * @param ViewAdvancedFilter $filter
		 *
		 * @return boolean
		 */
		public function isEqualTo ($filter) {
			if (
				(empty ($filter)) ||
				(!($filter instanceof ViewAdvancedFilter)) ||
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
		 * Valida que los atributos/valores (columna, operador, tipo de dato, nombre del campo, etiqueta, secuencia) de la vista esten correctos
		 *
		 * @throws ViewAdvancedFilterException
		 */
		public function validate () {
			if (empty ($this->columnName)) {
				throw new ViewAdvancedFilterException (ViewAdvancedFilterException::ERROR_VIEW_ADVANCED_FILTER_EMPTY_COLUMN_NAME);
			} else if (empty ($this->comparator)) {
				throw new ViewAdvancedFilterException (ViewAdvancedFilterException::ERROR_VIEW_ADVANCED_FILTER_EMPTY_COMPARATOR);
			} else if (empty ($this->dataType)) {
				throw new ViewAdvancedFilterException (ViewAdvancedFilterException::ERROR_VIEW_ADVANCED_FILTER_EMPTY_DATA_TYPE);
			} else if (empty ($this->fieldName)) {
				throw new ViewAdvancedFilterException (ViewAdvancedFilterException::ERROR_VIEW_ADVANCED_FILTER_EMPTY_FIELD_NAME);
			} else if (empty ($this->label)) {
				throw new ViewAdvancedFilterException (ViewAdvancedFilterException::ERROR_VIEW_ADVANCED_FILTER_EMPTY_LABEL);
			} else if (!isset ($this->sequence)) {
				throw new ViewAdvancedFilterException (ViewAdvancedFilterException::ERROR_VIEW_ADVANCED_FILTER_EMPTY_SEQUENCE);
			}
		}

		/**
		 * Instanciación de la clase ViewAdvancedFilter. Se obtiene un objeto ViewAdvancedFilter con los valores de la clase
		 *
		 * @param Field|null $field
		 * @param array|null $dummyData
		 *
		 * @return ViewAdvancedFilter
		 */
		public static function getInstance ($field = null, $dummyData = null) {
			return new self ($field, $dummyData);
		}

	}
