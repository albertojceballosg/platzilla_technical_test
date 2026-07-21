<?php
	require_once ('include/platzilla/Exceptions/ViewColumnException.php');
	require_once ('include/platzilla/Objects/Field.php');

	/**
	 * Class ViewColumn
	 *
	 * Esta clase "Vista Columna" hace referencia a las vista que controla el aspecto visual del color de las columnas que conforman
	 * las Tablas en la "Plataforma" y/o "Instancia". La clase está asociada al objeto "Campo"
	 */
	class ViewColumn {
		/** @var string */
		private $columnName;

		/** @var string */
		private $dataType;

		/** @var integer */
		private $fieldId;

		/** @var string */
		private $fieldName;

		/** @var string */
		private $label;

		/** @var string */
		private $moduleName;

		/** @var integer */
		private $sequence;

		/** @var string */
		private $tableName;

		/** @var integer */
		private $viewId;

		/**
		 * ViewColumn constructor.
		 *
		 * @param null $field
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
		 * Para obtener el nombre de la columna para la vista de color de las tablas
		 *
		 * @return string
		 */
		public function getColumnName () {
			return $this->columnName;
		}

		/**
		 * Para obtener el tipo de dato se empleara en la vista de color para las columnas de las tablas
		 *
		 * @return string
		 */
		public function getDataType () {
			return $this->dataType;
		}

		/**
		 * Para obtener el ID del campo
		 *
		 * @return integer
		 */
		public function getFieldId() {
			return $this->fieldId;
		}

		/**
		 * Para obtener el nombre del campo se empleara en la vista de color para las columnas de las tablas
		 *
		 * @return string
		 */
		public function getFieldName () {
			return $this->fieldName;
		}

		/**
		 * Para obtener la etiqueta en la vista de color para las columnas de las tablas
		 *
		 * @return string
		 */
		public function getLabel () {
			return $this->label;
		}

		/**
		 * Para obtener el nombre del modulo que se utilizara para la vista de color para las columnas de las tablas
		 *
		 * @return string
		 */
		public function getModuleName () {
			return $this->moduleName;
		}

		/**
		 * Para obtener la secuencia de la vista de color para las columnas de las tablas
		 *
		 * @return integer
		 */
		public function getSequence () {
			return $this->sequence;
		}

		/**
		 * Para obtener el nombre de la tabla donde se almacenara la vista de color para las columnas de las tablas
		 *
		 * @return string
		 */
		public function getTableName () {
			return $this->tableName;
		}

		/**
		 * Para obtener el id de la vista de color para las columnas de las tablas
		 *
		 * @return integer
		 */
		public function getViewId () {
			return $this->viewId;
		}

		/**
		 * Establece el nombre de la columna para la vista de color de las tablas
		 *
		 * @param string $columnName
		 *
		 * @return ViewColumn
		 */
		public function setColumnName ($columnName) {
			$this->columnName = $columnName;
			return $this;
		}

		/**
		 * Establece el tipo de dato se empleara en la vista de color para las columnas de las tablas
		 *
		 * @param string $dataType
		 *
		 * @return ViewColumn
		 */
		public function setDataType ($dataType) {
			if (in_array ($dataType, array (FieldInterface::DATA_TYPE_CHECKBOX, FieldInterface::DATA_TYPE_DATE, FieldInterface::DATA_TYPE_DATETIME, FieldInterface::DATA_TYPE_EMAIL, FieldInterface::DATA_TYPE_INTEGER, FieldInterface::DATA_TYPE_NEGATIVE_NUMBER, FieldInterface::DATA_TYPE_NUMBER, FieldInterface::DATA_TYPE_PASSWORD, FieldInterface::DATA_TYPE_TIME, FieldInterface::DATA_TYPE_VARCHAR))) {
				$this->dataType = $dataType;
			}
			return $this;
		}

		/**
		 * Establece el ID del campo
		 *
		 * @param integer $fieldId
		 *
		 * @return ViewColumn
		 */
		public function setFieldId($fieldId) {
			$this->fieldId = $fieldId;
			return $this;
		}

		/**
		 * Establece el nombre del campo se empleara en la vista de color para las columnas de las tablas
		 *
		 * @param string $fieldName
		 *
		 * @return ViewColumn
		 */
		public function setFieldName ($fieldName) {
			$this->fieldName = $fieldName;
			return $this;
		}

		/**
		 * Establece la etiqueta en la vista de color para las columnas de las tablas
		 *
		 * @param string $label
		 *
		 * @return ViewColumn
		 */
		public function setLabel ($label) {
			$this->label = $label;
			return $this;
		}

		/**
		 * Establece el nombre del modulo que se utilizara para la vista de color para las columnas de las tablas
		 *
		 * @param string $moduleName
		 *
		 * @return ViewColumn
		 */
		public function setModuleName ($moduleName) {
			$this->moduleName = $moduleName;
			return $this;
		}

		/**
		 * Establece la secuencia de la vista de color para las columnas de las tablas
		 *
		 * @param integer $sequence
		 *
		 * @return ViewColumn
		 */
		public function setSequence ($sequence) {
			$this->sequence = $sequence;
			return $this;
		}

		/**
		 * Establece el nombre de la tabla donde se almacenara la vista de color para las columnas de las tablas
		 *
		 * @param string $tableName
		 *
		 * @return ViewColumn
		 */
		public function setTableName ($tableName) {
			$this->tableName = $tableName;
			return $this;
		}

		/**
		 * Establece el id de la vista de color para las columnas de las tablas
		 *
		 * @param integer $viewId
		 *
		 * @return ViewColumn
		 */
		public function setViewId ($viewId) {
			$this->viewId = $viewId;
			return $this;
		}

		/**
		 * Realiza copia de los valores de la vista de color para las columnas de las tablas desde otra vista
		 *
		 * @param ViewColumn $column
		 */
		public function copyValuesFrom ($column) {
			if ((empty ($column)) || (!($column instanceof ViewColumn)) || ($this->isEqualTo ($column))) {
				return;
			}

			$this->columnName = $column->getColumnName ();
			$this->dataType   = $column->getDataType ();
			$this->fieldName  = $column->getFieldName ();
			$this->label      = $column->getLabel ();
			$this->moduleName = $column->getModuleName ();
			$this->sequence   = $column->getSequence ();
			$this->tableName  = $column->getTableName ();
		}

		/**
		 * Duplica los atributos (Viewid, nombre codigo de los campos) de la vista de color para las columnas de las tablas desde otra vista
		 *
		 * @param integer $newViewId
		 * @param string $oldCodeFieldName
		 * @param string $newCodeFieldName
		 *
		 * @return ViewColumn
		 * @throws ViewColumnException
		 */
		public function duplicate ($newViewId, $oldCodeFieldName = null, $newCodeFieldName = null) {
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
				->setSequence ($this->sequence)
				->setTableName ($this->tableName)
				->setViewId ($newViewId);
		}

		/**
		 * Para comparar si la vista de color para las columnas de las tablas es igual a otra
		 *
		 * @param ViewColumn $column
		 *
		 * @return boolean
		 */
		public function isEqualTo ($column) {
			if (
				(empty ($column)) ||
				(!($column instanceof ViewColumn)) ||
				($this->columnName != $column->getColumnName ()) ||
				($this->dataType != $column->getDataType ()) ||
				($this->fieldName != $column->getFieldName ()) ||
				($this->label != $column->getLabel ()) ||
				($this->moduleName != $column->getModuleName ()) ||
				($this->sequence != $column->getSequence ()) ||
				($this->tableName != $column->getTableName ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Valida que los atributos/valores (nombre de la columnas, tipo de dato, nombre del campo etiqueta y secuencia) de la vista de color para las columnas esten correctos
		 *
		 * @throws ViewColumnException
		 */
		public function validate () {
			if (empty ($this->columnName)) {
				throw new ViewColumnException (ViewColumnException::ERROR_VIEW_COLUMN_EMPTY_COLUMN_NAME);
			} else if (empty ($this->dataType)) {
				throw new ViewColumnException (ViewColumnException::ERROR_VIEW_COLUMN_EMPTY_DATA_TYPE);
			} else if (empty ($this->fieldName)) {
				throw new ViewColumnException (ViewColumnException::ERROR_VIEW_COLUMN_EMPTY_FIELD_NAME);
			} else if (empty ($this->label)) {
				throw new ViewColumnException (ViewColumnException::ERROR_VIEW_COLUMN_EMPTY_LABEL);
			} else if (!isset ($this->sequence)) {
				throw new ViewColumnException (ViewColumnException::ERROR_VIEW_COLUMN_EMPTY_SEQUENCE);
			}
		}

		/**
		 * Instanciación de la clase ViewColumn. Se obtiene un objeto ViewColumn con los valores de la clase
		 *
		 * @param Field|null $field
		 *
		 * @return ViewColumn
		 */
		public static function getInstance ($field = null) {
			return new self ($field);
		}

	}
