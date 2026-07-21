<?php
	require_once ('include/platzilla/Exceptions/GridFieldException.php');
	require_once ('include/platzilla/Objects/GridFieldInterface.php');
	require_once ('include/platzilla/Objects/FieldModuleReference.php');

	/**
	 * Class GridField
	 *
	 * Esta clase define el objeto "Campo Grid" el cual hace referencia a los campos del tipo "Grid" o tablas inteligentes que contiene un Módulo.
	 * La clase está asociada al objeto "Campo Referencia a Módulo"
	 */
	class GridField implements GridFieldInterface {
		/** @var string */
		private $actionField;

		/** @var string */
		private $dataField;

		/** @var string */
		private $defaultValue;

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
		 * Para obtener los valores por defecto del campo grid
		 *
		 * @return GridFieldValues
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
		 * @return GridField
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
		 * @return GridField
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
		 * @return GridField
		 */
		public function setDefaultValue ($defaultValue) {
			$this->defaultValue = $defaultValue;
			return $this;
		}

		/**
		 * Establece los valores por defecto del campo grid
		 *
		 * @param $fieldValues
		 *
		 * @return GridField
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
		 * @return GridField
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
		 * @return GridField
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
		 * @return GridField
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
		 * @return GridField
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
		 * @return GridField
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
		 * @return GridField
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
		 * @return GridField
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
		 * @return GridField
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
		 * @return GridField
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
		 * @return GridField
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
		 * @return GridField
		 */
		public function setValues ($values) {
			$this->values = $values;
			return $this;
		}

		/**
		 * Copia los atributos del campo grid desde otro
		 *
		 * @param GridField $field
		 */
		public function copyValuesFrom ($field) {
			if ((empty ($field)) || (!($field instanceof GridField))) {
				return;
			}

			$this->actionField      = $field->getActionField ();
			$this->dataField        = $field->getDataField ();
			$this->defaultValue     = $field->getDefaultValue ();
			$this->filterField      = $field->getFilterField ();
			$this->label            = $field->getLabel ();
			$this->length           = $field->getLength ();
			$this->moduleReferences = $field->getModuleReferences ();
			$this->name             = $field->getName ();
			$this->precision        = $field->getPrecision ();
			$this->sequence         = $field->getSequence ();
			$this->uiType           = $field->getUiType ();
			$this->values           = $field->getValues ();
			$this->locked           = $field->isLocked ();
		}

		/**
		 * Duplica el campo grid con todos sus atributos/valores
		 *
		 * @return GridField
		 * @throws GridFieldException
		 */
		public function duplicate () {
			$object = new self ();
			return $object->setActionField ($this->actionField)
				->setDataField ($this->dataField)
				->setDefaultValue ($this->defaultValue)
				->setFilterField ($this->filterField)
				->setLabel ($this->label)
				->setLength ($this->length)
				->setModuleReferences ($this->moduleReferences)
				->setName ($this->name)
				->setPrecision ($this->precision)
				->setSequence ($this->sequence)
				->setUiType ($this->uiType)
				->setValues ($this->values)
				->setLocked ($this->locked);
		}

		/**
		 * Compara si un campo grid con sus atributos/valores es igual a otro
		 *
		 * @param GridField $field
		 *
		 * @return boolean
		 */
		public function isEqualTo ($field) {
			if (
				(empty ($field)) ||
				(!($field instanceof GridField)) ||
				($this->actionField != $field->getActionField ()) ||
				($this->dataField != $field->getDataField ()) ||
				($this->defaultValue != $field->getDefaultValue ()) ||
				($this->filterField != $field->getFilterField ()) ||
				($this->label != $field->getLabel ()) ||
				($this->length != $field->getLength ()) ||
				($this->moduleReferences != $field->getModuleReferences ()) ||
				($this->name != $field->getName ()) ||
				($this->precision != $field->getPrecision ()) ||
				($this->sequence != $field->getSequence ()) ||
				($this->uiType != $field->getUiType ()) ||
				($this->values != $field->getValues ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Instanciación de la clase GridField. Se obtiene un objeto GridField con los atributos de la clase
		 *
		 * @return GridField
		 */
		public static function getInstance () {
			return new self ();
		}

	}
