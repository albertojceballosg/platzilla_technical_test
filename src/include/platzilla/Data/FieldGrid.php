<?php
	require_once ('include/platzilla/Data/FieldGridException.php');
	require_once ('include/platzilla/Objects/GridFieldInterface.php');

	/**
	 * Class FieldGrid
	 *
	 * Esta clase define el objeto "Campo Grid" el cual hace referencia a los campos del tipo "Grid" o tablas inteligentes que contiene un Módulo.
	 */
	class FieldGrid implements GridFieldInterface {
		/** @var string */
		private $actionField;

		/** @var string */
		private $dataField;

		/** @var string */
		private $defaultValue;

		/** @var integer */
		private $fieldid;

		/** @var string */
		private $fieldname;

		/** @var string */
		private $fieldlabel;

		/** @var GridFieldValues */
		private $fieldValues;

		/** @var string */
		private $filterField;

		/** @var string */
		private $label;

		/** @var integer */
		private $length;

		/** @var boolean */
		private $locked;

		/** @var string */
		private $moduleReferences;

		/** @var string */
		private $name;

		/** @var integer */
		private $precision;

		/** @var integer */
		private $sequence;

		/** @var integer */
		private $subFieldsid;

		/** @var integer */
		private $uiType;

		/** @var string */
		private $values;

		/**
		 * Para obtener la accion del campo grid
		 *
		 * @return string
		 */
		public function getActionField () {
			return $this->actionField;
		}

		/**
		 * Para obtener los datos del campo grid
		 *
		 * @return string
		 */
		public function getDataField () {
			return $this->dataField;
		}

		/**
		 * Para obtener los valores por defecto del grid
		 *
		 * @return string
		 */
		public function getDefaultValue () {
			return $this->defaultValue;
		}

		/**
		 * Para obtener el ID de el campo Grid
		 *
		 * @return integer
		 */
		public function getFieldId () {
			return $this->fieldid;
		}

		/**
		 * Para obtener la Etiqueta de el campo Grid
		 *
		 * @return string
		 */
		public function getFieldLabel () {
			return $this->fieldlabel;
		}

		/**
		 * Para obtener el Nombre de el campo Grid
		 *
		 * @return string
		 */
		public function getFieldName () {
			return $this->fieldname;
		}

		/**
		 * Para obtener los valores por defecto del campo grid
		 *
		 * @return string
		 */
		public function getFieldValues () {
			return $this->fieldValues;
		}

		/**
		 * Para obtener el filtro asociado al grid
		 *
		 * @return string
		 */
		public function getFilterField () {
			return $this->filterField;
		}

		/**
		 * Para obtener la etiqueta del grid
		 *
		 * @return string
		 */
		public function getLabel () {
			return $this->label;
		}

		/**
		 * Para obtener la longitud del grid
		 *
		 * @return integer
		 */
		public function getLength () {
			return $this->length;
		}

		/**
		 * Para obtener la referencia del modulo vinculado en los campos del grid
		 *
		 * @return string
		 */
		public function getModuleReferences () {
			return $this->moduleReferences;
		}

		/**
		 * Para obtener el nombre del grid
		 *
		 * @return string
		 */
		public function getName () {
			return $this->name;
		}

		/**
		 * Para obtener la precision de los calculos realice el grid
		 *
		 * @return integer
		 */
		public function getPrecision () {
			return $this->precision;
		}

		/**
		 * Para obtener la secuencia del grid
		 *
		 * @return integer
		 */
		public function getSequence () {
			return $this->sequence;
		}

		/**
		 * Para obtener el id de los campos tendra el grid
		 *
		 * @return integer
		 */
		public function getSubFieldId () {
			return $this->subFieldsid;
		}

		/**
		 * Para obtener el tipo de dato a nivel de la BD del campo
		 *
		 * @return integer
		 */
		public function getUiType () {
			return $this->uiType;
		}

		/**
		 * Para obtener el valor del grid
		 *
		 * @return string
		 */
		public function getValues () {
			return $this->values;
		}

		/**
		 * Obtiene el valor de la bandera que controla si el grid puede bloquearse o no
		 *
		 * @return boolean
		 */
		public function isLocked () {
			return $this->locked;
		}

		/**
		 * Establece la accion del campo grid
		 *
		 * @param string $actionField
		 *
		 * @return FieldGrid
		 */
		public function setActionField ($actionField) {
			$this->actionField = $actionField;
			return $this;
		}

		/**
		 * Establece los datos del campo grid
		 *
		 * @param string $dataField
		 *
		 * @return FieldGrid
		 */
		public function setDataField ($dataField) {
			$this->dataField = $dataField;
			return $this;
		}

		/**
		 * Establece los valores por defecto del grid
		 *
		 * @param string $defaultValue
		 *
		 * @return FieldGrid
		 */
		public function setDefaultValue ($defaultValue) {
			$this->defaultValue = $defaultValue;
			return $this;
		}

		/**
		 * Establese el ID del campo Grid
		 *
		 * @param integer $fieldId
		 *
		 * @return FieldGrid
		 */
		public function setFieldId ($fieldId) {
			$this->fieldid = $fieldId;
			return $this;
		}

		/**
		 * Establese la Etiqueta  del campo Grid
		 *
		 * @param string $fieldLabel
		 *
		 * @return FieldGrid
		 */
		public function setFieldLabel ($fieldLabel) {
			$this->fieldlabel = $fieldLabel;
			return $this;
		}

		/**
		 * Establese el Nombre  del campo Grid
		 *
		 * @param string $fieldName
		 *
		 * @return FieldGrid
		 */
		public function setFieldName ($fieldName) {
			$this->fieldname = $fieldName;
			return $this;
		}

		/**
		 * Establece los valores por defecto del campo grid
		 *
		 * @param $fieldValues
		 *
		 * @return FieldGrid
		 */
		public function setFieldValues ($fieldValues) {
			$this->fieldValues = $fieldValues;
			return $this;
		}

		/**
		 * Establece el filtro asociado al grid
		 *
		 * @param string $filterField
		 *
		 * @return FieldGrid
		 */
		public function setFilterField ($filterField) {
			$this->filterField = $filterField;
			return $this;
		}

		/**
		 * Establece la etiqueta del grid
		 *
		 * @param string $label
		 *
		 * @return FieldGrid
		 */
		public function setLabel ($label) {
			$this->label = $label;
			return $this;
		}

		/**
		 * Establece la longitud del grid
		 *
		 * @param integer $length
		 *
		 * @return FieldGrid
		 */
		public function setLength ($length) {
			$this->length = $length;
			return $this;
		}

		/**
		 * Establece el valor de la bandera que controla si el grid puede bloquearse o no
		 *
		 * @param boolean $locked
		 *
		 * @return FieldGrid
		 */
		public function setLocked ($locked) {
			if (is_bool ($locked)) {
				$this->locked = $locked;
			}
			return $this;
		}

		/**
		 * Establece la referencia del modulo vinculado en los campos del grid
		 *
		 * @param string $moduleReferences
		 *
		 * @return FieldGrid
		 */
		public function setModuleReferences ($moduleReferences) {
			$this->moduleReferences = $moduleReferences;
			return $this;
		}

		/**
		 * Establece el nombre del grid
		 *
		 * @param string $name
		 *
		 * @return FieldGrid
		 */
		public function setName ($name) {
			$this->name = $name;
			return $this;
		}

		/**
		 * Establece la precision de los calculos realice el grid
		 *
		 * @param integer $precision
		 *
		 * @return FieldGrid
		 */
		public function setPrecision ($precision) {
			$this->precision = $precision;
			return $this;
		}

		/**
		 * Establece la secuencia del grid
		 *
		 * @param integer $sequence
		 *
		 * @return FieldGrid
		 */
		public function setSequence ($sequence) {
			$this->sequence = $sequence;
			return $this;
		}

		/**
		 * Establece el id de los campos tendra el grid
		 *
		 * @param string $subFieldId
		 *
		 * @return FieldGrid
		 */
		public function setSubFieldId ($subFieldId) {
			$this->subFieldsid = $subFieldId;
			return $this;
		}

		/**
		 * Establece el tipo de dato a nivel de la BD del campo
		 *
		 * @param integer $uiType
		 *
		 * @return FieldGrid
		 */
		public function setUiType ($uiType) {
			$this->uiType = $uiType;
			return $this;
		}

		/**
		 * Establece el valor del grid
		 *
		 * @param string $values
		 *
		 * @return FieldGrid
		 */
		public function setValues ($values) {
			$this->values = $values;
			return $this;
		}

		/**
		 * Instanciación de la clase FieldGrid. Se obtiene un objeto FieldGrid con los atributos de la clase
		 *
		 * @return FieldGrid
		 */
		public static function getInstance () {
			return new self ();
		}

	}
