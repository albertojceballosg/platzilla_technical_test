<?php
	require_once ('include/platzilla/Exceptions/FieldValidationException.php');
	require_once ('include/platzilla/Objects/FieldValidationInterface.php');

	/**
	 * Class FieldValidation
	 *
	 * En esta clase se define el objeto "Validaciones de Campo" el cual  hace referencia a las validaciones que se aplican a un campo perteneciente a un Módulo.
	 */
	class FieldValidation implements FieldValidationInterface {
		/** @var string */
		private $fieldName;

		/** @var string */
		private $initialValue;

		/** @var boolean */
		private $locked;

		/** @var string */
		private $maximumValue;

		/** @var string */
		private $moduleName;

		/** @var string */
		private $tableName;

		/** @var string */
		private $type;

		/**
		 * FieldValidation Constructor
		 *
		 */
		public function __construct () {
			$this->locked  = false;
		}

		/**
		 * Para obtener el nombre del campo asociado al modulo
		 *
		 * @return string Nombre del campo
		 */
		public function getFieldName () {
			return $this->fieldName;
		}

		/**
		 * Para obtener el valor de inicializacion del campo
		 *
		 * @return string Valor de inicializacion del campo
		 */
		public function getInitialValue () {
			return $this->initialValue;
		}

		/**
		 * Para obtener el valor maximo que puede tomar el campo
		 *
		 * @return string Valor maximo del campo
		 */
		public function getMaximumValue () {
			return $this->maximumValue;
		}

		/**
		 * Para obtener el nombre del modulo asociado al campo
		 *
		 * @return string Nombre del modulo
		 */
		public function getModuleName () {
			return $this->moduleName;
		}

		/**
		 * Para obtener el nombre de la tabla del modulo donde se almacena el campo
		 *
		 * @return string Nombre de la tabla
		 */
		public function getTableName () {
			return $this->tableName;
		}

		/**
		 * Para obtener el tipo de campo
		 *
		 * @return string El tipo de campo
		 */
		public function getType () {
			return $this->type;
		}

		/**
		 * Obtiene el valor de la bandera que controla si el campo puede bloquearse o no
		 *
		 * @return boolean <code>true</code> si el campo se bloquea. <code>false</code> en caso contrario
		 */
		public function isLocked () {
			return $this->locked;
		}

		/**
		 * Establece la validacion para el nombre del campo
		 *
		 * @param string $fieldName Para el nombre del campo
		 *
		 * @return FieldValidation
		 */
		public function setFieldName ($fieldName) {
			$this->fieldName = $fieldName;
			return $this;
		}

		/**
		 * Establece la validacion para el valor inicial para el campo
		 *
		 * @param string $initialValue
		 *
		 * @return FieldValidation
		 */
		public function setInitialValue ($initialValue) {
			$this->initialValue = $initialValue;
			return $this;
		}

		/**
		 * Establece la validacion de la bandera que controla si el campo puede bloquearse o no
		 *
		 * @param boolean $locked
		 *
		 * @return FieldValidation
		 */
		public function setLocked ($locked) {
			if (is_bool ($locked)) {
				$this->locked = $locked;
			}
			return $this;
		}

		/**
		 * Establece la validacion para el valor maximo puede tomar el campo
		 *
		 * @param string $maximumValue
		 *
		 * @return FieldValidation
		 */
		public function setMaximumValue ($maximumValue) {
			$this->maximumValue = $maximumValue;
			return $this;
		}

		/**
		 * Establece la validacion para el nombre del modulo
		 *
		 * @param string $moduleName
		 *
		 * @return FieldValidation
		 */
		public function setModuleName ($moduleName) {
			$this->moduleName = $moduleName;
			return $this;
		}

		/**
		 * Establece la validacion para el nombre de la tabla donde se almacena el campo
		 *
		 * @param string $tableName
		 *
		 * @return FieldValidation
		 */
		public function setTableName ($tableName) {
			$this->tableName = $tableName;
			return $this;
		}

		/**
		 * Establece el tipo de validacion para el campo: Campo Fecha, Campo Numerico y campo unico
		 *
		 * @param string $type
		 *
		 * @return FieldValidation
		 */
		public function setType ($type) {
			if (in_array ($type, array (self::VALIDATION_TYPE_DATE, self::VALIDATION_TYPE_NUMBER, self::VALIDATION_TYPE_UNIQUE))) {
				$this->type = $type;
			}
			return $this;
		}

		/**
		 * Para realizar copia de las validaciones asociadas al campo
		 *
		 * @param FieldValidation $validation
		 */
		public function copyValuesFrom ($validation) {
			if ((empty ($validation)) || (!($validation instanceof FieldValidation))) {
				return;
			}

			$this->fieldName    = $validation->getFieldName ();
			$this->initialValue = $validation->getInitialValue ();
			$this->maximumValue = $validation->getMaximumValue ();
			$this->moduleName   = $validation->getModuleName ();
			$this->tableName    = $validation->getTableName ();
			$this->type         = $validation->getType ();
		}

		/**
		 * Realiza duplicacion de las validaciones asociadas al campo
		 *
		 * @return FieldValidation
		 * @throws FieldValidationException
		 */
		public function duplicate () {
			$this->validate ();

			$object = new self ();
			return $object->setFieldName ($this->fieldName)
				->setInitialValue ($this->initialValue)
				->setMaximumValue ($this->maximumValue)
				->setModuleName ($this->moduleName)
				->setTableName ($this->tableName)
				->setType ($this->type);
		}

		/**
		 * Compara si las validaciones del campo son iguales a otro
		 *
		 * @param FieldValidation $validation
		 *
		 * @return boolean
		 */
		public function isEqualTo ($validation) {
			if (
				(empty ($validation)) ||
				(!($validation instanceof FieldValidation)) ||
				($this->fieldName != $validation->getFieldName ()) ||
				($this->initialValue != $validation->getInitialValue ()) ||
				($this->maximumValue != $validation->getMaximumValue ()) ||
				($this->moduleName != $validation->getModuleName ()) ||
				($this->tableName != $validation->getTableName ()) ||
				($this->type != $validation->getType ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Para validar las excepciones asociadas a las validaciones de los campos
		 *
		 * @throws FieldValidationException Si la validacion del campo no es valida
		 */
		public function validate () {
			if (empty ($this->fieldName)) {
				throw new FieldValidationException (FieldValidationException::ERROR_FIELD_VALIDATION_EMPTY_FIELD_NAME);
			} else if (empty ($this->moduleName)) {
				throw new FieldValidationException (FieldValidationException::ERROR_FIELD_VALIDATION_EMPTY_MODULE_NAME);
			} else if (empty ($this->tableName)) {
				throw new FieldValidationException (FieldValidationException::ERROR_FIELD_VALIDATION_EMPTY_TABLE_NAME);
			} else if (empty ($this->type)) {
				throw new FieldValidationException (FieldValidationException::ERROR_FIELD_VALIDATION_EMPTY_TYPE);
			}
		}

		/**
		 * Instanciación de la clase FieldValidation. Se obtiene un objeto FieldValidation con los atributos de la clase
		 *
		 * @return FieldValidation
		 */
		public static function getInstance () {
			return new self ();
		}

	}
