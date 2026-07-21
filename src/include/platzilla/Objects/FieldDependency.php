<?php
	require_once ('include/platzilla/Exceptions/FieldDependencyException.php');
	require_once ('include/platzilla/Objects/FieldDependencyInterface.php');

	/**
	 * Class FieldDependency
	 *
	 * Esta clase define el objeto "Dependencia Campo" el cual hace referencia a las dependencias que se pueden establecer entre los campos que componen un Modulo
	 */
	class FieldDependency implements FieldDependencyInterface {
		/** @var string */
		private $moduleName;

		/** @var string */
		private $sourceFieldName;

		/** @var string */
		private $sourceFieldValue;

		/** @var string */
		private $targetFieldName;

		/** @var integer */
		private $targetFieldVisibility;

		/**
		 * FieldDependency constructor.
		 */
		public function __construct () {
			$this->targetFieldVisibility = self::VISIBILITY_VISIBLE;
		}

		/**
		 * Para obtener el nombre del modulo que contiene los campos donde se estableceran dependencias
		 *
		 * @return string
		 */
		public function getModuleName () {
			return $this->moduleName;
		}

		/**
		 * Para obtener el nombre del campo fuente para la dependencia
		 *
		 * @return string
		 */
		public function getSourceFieldName () {
			return $this->sourceFieldName;
		}

		/**
		 * Para obtener el valor fuente del campo con la dependencia
		 *
		 * @return string
		 */
		public function getSourceFieldValue () {
			return $this->sourceFieldValue;
		}

		/**
		 * Obtiene el nombre del campo destino para la dependencia
		 *
		 * @return string
		 */
		public function getTargetFieldName () {
			return $this->targetFieldName;
		}

		/**
		 * Obtiene la visibilidad (visiblo o oculo) del campo destino para la dependencia
		 *
		 * @return integer
		 */
		public function getTargetFieldVisibility () {
			if (!in_array ($this->targetFieldVisibility, array (self::VISIBILITY_HIDDEN, self::VISIBILITY_VISIBLE))) {
				return self::VISIBILITY_VISIBLE;
			} else {
				return $this->targetFieldVisibility;
			}
		}

		/**
		 * Establece el nombre del campo
		 *
		 * @param string $moduleName
		 *
		 * @return FieldDependency
		 */
		public function setModuleName ($moduleName) {
			$this->moduleName = $moduleName;
			return $this;
		}

		/**
		 * Establece el nombre del campo fuente para la dependencia
		 *
		 * @param string $sourceFieldName
		 *
		 * @return FieldDependency
		 */
		public function setSourceFieldName ($sourceFieldName) {
			$this->sourceFieldName = $sourceFieldName;
			return $this;
		}

		/**
		 * Establece el valor del campo fuente para la dependencia
		 *
		 * @param string $sourceFieldValue
		 *
		 * @return FieldDependency
		 */
		public function setSourceFieldValue ($sourceFieldValue) {
			$this->sourceFieldValue = $sourceFieldValue;
			return $this;
		}

		/**
		 * Establece el nombre del campo destino para la dependencia
		 *
		 * @param string $targetFieldName
		 *
		 * @return FieldDependency
		 */
		public function setTargetFieldName ($targetFieldName) {
			$this->targetFieldName = $targetFieldName;
			return $this;
		}

		/**
		 * Establece la visibilidad (visiblo o oculo) del campo destino para la dependencia
		 *
		 * @param integer $targetFieldVisibility
		 *
		 * @return FieldDependency
		 */
		public function setTargetFieldVisibility ($targetFieldVisibility) {
			if (in_array ($targetFieldVisibility, array (self::VISIBILITY_HIDDEN, self::VISIBILITY_VISIBLE))) {
				$this->targetFieldVisibility = $targetFieldVisibility;
			}
			return $this;
		}

		/**
		 * Copia los valores/atributos de las dependencias establecidas del campo
		 *
		 * @param FieldDependency $dependency
		 */
		public function copyValuesFrom ($dependency) {
			if ((empty ($dependency)) || (!($dependency instanceof FieldDependency))) {
				return;
			}

			$this->moduleName            = $dependency->getModuleName ();
			$this->sourceFieldName       = $dependency->getSourceFieldName ();
			$this->sourceFieldValue      = $dependency->getSourceFieldValue ();
			$this->targetFieldName       = $dependency->getTargetFieldName ();
			$this->targetFieldVisibility = $dependency->getTargetFieldVisibility ();
		}

		/**
		 * Duplica los valores/atributos de las dependencias establecidas del campo
		 *
		 * @return FieldDependency
		 * @throws FieldDependencyException
		 */
		public function duplicate () {
			$this->validate ();

			$object = new self ();
			return $object->setModuleName ($this->moduleName)
				->setSourceFieldName ($this->sourceFieldName)
				->setSourceFieldValue ($this->sourceFieldValue)
				->setTargetFieldName ($this->targetFieldName)
				->setTargetFieldVisibility ($this->targetFieldVisibility);
		}

		/**
		 * Compara si las dependencias de un campo son igualaes a otro
		 *
		 * @param FieldDependency $dependency
		 *
		 * @return boolean
		 */
		public function isEqualTo ($dependency) {
			if (
				(empty ($dependency)) ||
				(!($dependency instanceof FieldDependency)) ||
				($this->moduleName != $dependency->getModuleName ()) ||
				($this->sourceFieldName != $dependency->getSourceFieldName ()) ||
				($this->sourceFieldValue != $dependency->getSourceFieldValue ()) ||
				($this->targetFieldName != $dependency->getTargetFieldName ()) ||
				($this->targetFieldVisibility != $dependency->getTargetFieldVisibility ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Valida que los valores/atributos de la dependencia del campo sean validas
		 *
		 * @throws FieldDependencyException
		 */
		public function validate () {
			if (empty ($this->moduleName)) {
				throw new FieldDependencyException (FieldDependencyException::ERROR_FIELD_DEPENDENCY_EMPTY_MODULE_NAME);
			} else if (empty ($this->sourceFieldName)) {
				throw new FieldDependencyException (FieldDependencyException::ERROR_FIELD_DEPENDENCY_EMPTY_SOURCE_FIELD_NAME);
			} else if (empty ($this->targetFieldName)) {
				throw new FieldDependencyException (FieldDependencyException::ERROR_FIELD_DEPENDENCY_EMPTY_TARGET_FIELD_NAME);
			}
		}

		/**
		 * Instanciación de la clase FielDependency. Se obtiene un objeto FieldDependency con los atributos de la clase
		 *
		 * @return FieldDependency
		 */
		public static function getInstance () {
			return new self ();
		}

	}
