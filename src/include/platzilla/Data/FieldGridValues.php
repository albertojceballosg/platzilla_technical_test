<?php
	require_once ('include/platzilla/Data/FieldGridException.php');
	/**
	 * Class FieldGridValues
	 *
	 * Esta clase hace referencia a los métodos que gestionan la tabla vtiger_subfields_values
	 */
	class FieldGridValues {
		/** @var string */
		private $fieldlabel;

		/** @var string */
		private $fieldname;

		/** @var array */
		private $gridFieldArrayValue;

		/** @var string */
		private $gridFieldValue;

		/** @var integer */
		private $modulecfId;

		/** @var integer */
		private $subfieldsid;

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
		 * Para obtener los valores de los campos que contiene el grid
		 *
		 * @return array
		 */
		public function getGridFieldArrayValue () {
			return $this->gridFieldArrayValue;
		}

		/**
		 * Para obtener el valor inicial del grid
		 *
		 * @return string
		 */
		public function getGridFieldValue () {
			return $this->gridFieldValue;
		}

		/**
		 * Para obtener el id del modulo del campo personalizado se incluya en el grid
		 *
		 * @return integer
		 */
		public function getModulecfId () {
			return $this->modulecfId;
		}

		/**
		 * Para obtener el id del campo se incluiran en el grid
		 *
		 * @return integer
		 */
		public function getSubFieldId () {
			return $this->subfieldsid;
		}

		/**
		 * Establese la Etiqueta  del campo Grid
		 *
		 * @param string $fieldLabel
		 *
		 * @return FieldGridValues
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
		 * @return FieldGridValues
		 */
		public function setFieldName ($fieldName) {
			$this->fieldname = $fieldName;
			return $this;
		}

		/**
		 * Establece los valores iniciales del grid
		 *
		 * @param string|array $fieldValue
		 *
		 * @return FieldGridValues
		 */
		public function setGridFieldValue ($fieldValue) {
			if (is_array ($fieldValue)) {
				$this->gridFieldValue      = base64_encode (serialize ($fieldValue));
				$this->gridFieldArrayValue = $fieldValue;
			} else if (!empty($fieldValue)) {
				$this->gridFieldArrayValue = unserialize (base64_decode ($fieldValue));
				$this->gridFieldValue      = $fieldValue;
			}
			return $this;
		}

		/**
		 * Establece el id del modulo del campo personalizado se incluya en el grid
		 *
		 * @param integer $modulecfId
		 *
		 * @return FieldGridValues
		 */
		public function setModulecfId ($modulecfId) {
			if (is_numeric($modulecfId) && ($modulecfId > 0) && (intval ($modulecfId) == $modulecfId)) {
				$this->modulecfId = $modulecfId;
			} else {
				$this->modulecfId = 0;
			}
			return $this;
		}

		/**
		 * Establece el id del campo se incluiran en el grid
		 *
		 * @param integer $subFieldId
		 *
		 * @return FieldGridValues
		 */
		public function setSubFieldId ($subFieldId) {
			if (is_numeric($subFieldId) && ($subFieldId > 0) && (intval ($subFieldId) == $subFieldId)) {
				$this->subfieldsid = $subFieldId;
			} else {
				$this->subfieldsid = 0;
			}
			return $this;
		}

		/**
		 * @throws FieldGridException
		 */
		public function validate () {
			if (!$this->modulecfId) {
				throw new FieldGridException (FieldGridException::ERROR_GRID_FIELD_EMPTY_ENTYTI_ID);
			} else if (!$this->subfieldsid) {
				throw new FieldGridException (FieldGridException::ERROR_GRID_FIELD_EMPTY_SUBFIELD_ID);
			}
		}

		/**
		 * Instanciación de la clase FieldGridValues. Se obtiene un objeto GridField con los valores de la clase
		 *
		 * @return FieldGridValues
		 */
		public static function getInstance () {
			return new self ();
		}

	}
