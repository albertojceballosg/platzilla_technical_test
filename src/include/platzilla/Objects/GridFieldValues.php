<?php
	require_once ('include/platzilla/Exceptions/GridFieldException.php');
	require_once ('include/platzilla/Objects/GridFieldInterface.php');
	require_once ('include/platzilla/Objects/FieldModuleReference.php');

	/**
	 * Class GridFieldValues
	 *
	 * Esta clase "Valores Campo Grid" hace referencia a los valores que puede tomar un campo del tipo "Grid" o tabla inteligente que contiene un Módulo.
	 * La clase está asociada al objeto "Campo Referencia a Módulo".
	 */
	class GridFieldValues {
		/** @var integer */
		private $modulecfId;

		/** @var integer */
		private $subfieldsid;

		/** @var string */
		private $gridFieldValue;

		/** @var array */
		private $gridFieldArrayValue;

		/**
		 * Para obtener el valor inicial del grid
		 *
		 * @return string
		 */
		public function getGridFieldValue () {
			return $this->gridFieldValue;
		}

		/**
		 * Para obtener los valores de los campos que contiene el grid
		 *
		 * @return string
		 */
		public function getGridFieldArrayValue () {
			return $this->gridFieldArrayValue;
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
		 * Establece los valores iniciales del grid
		 *
		 * @param string|array $fieldValue
		 *
		 * @return GridFieldValues
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
		 * @return GridFieldValues
		 */
		public function setModulecfId ($modulecfId) {
			$this->modulecfId = $modulecfId;
			return $this;
		}

		/**
		 * Establece el id del campo se incluiran en el grid
		 *
		 * @param string $subFieldId
		 *
		 * @return GridFieldValues
		 */
		public function setSubFieldId ($subFieldId) {
			$this->subfieldsid = $subFieldId;
			return $this;
		}

		/**
		 * Copia los valores del campo grid desde otro
		 *
		 * @param GridFieldValues $field
		 */
		public function copyValuesFrom ($field) {
			if ((empty ($field)) || (!($field instanceof GridFieldValues))) {
				return;
			}
			$this->subfieldsid    = $field->getSubFieldId ();
			$this->modulecfId     = $field->getModulecfId ();
			$this->gridFieldValue = $field->getGridFieldValue ();
		}

		/**
		 * Duplica los valores del grid
		 *
		 * @return GridFieldValues
		 */
		public function duplicate () {
			$object = new self ();
			return $object->setGridFieldValue ($this->gridFieldValue)
				->setModulecfId ($this->modulecfId)
				->setSubFieldId ($this->subfieldsid);
		}

		/**
		 * Compara si los valores del campo grid son iguales a otro
		 *
		 * @param GridField $field
		 *
		 * @return boolean
		 */
		public function isEqualTo ($field) {
			if (
				(empty ($field)) ||
				(!($field instanceof GridFieldValues)) ||
				($this->gridFieldValue != $field->getGridFieldValue ()) ||
				($this->modulecfId != $field->getModulecfId ()) ||
				($this->subfieldsid != $field->getSubFieldId ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Instanciación de la clase GridFieldValues. Se obtiene un objeto GridField con los valores de la clase
		 *
		 * @return GridField
		 */
		public static function getInstance () {
			return new self ();
		}

	}
