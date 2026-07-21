<?php
	require_once ('include/platzilla/Exceptions/FieldProfileException.php');
	require_once ('include/platzilla/Objects/FieldProfileInterface.php');

	/**
	 * Class FieldProfile
	 *
	 * En esta clase se define el objeto "Perfil de Campo" el cual  hace referencia a los permisos que se pueden asignar a un campo de un Módulo.
	 */
	class FieldProfile implements FieldProfileInterface {
		/** @var string */
		private $fieldName;

		/** @var string */
		private $moduleName;

		/** @var string */
		private $profileName;

		/** @var integer */
		private $readOnly;

		/** @var integer */
		private $visibility;

		/**
		 * FieldProfile constructor.
		 */
		public function __construct () {
			$this->readOnly   = self::READ_WRITE;
			$this->visibility = self::VISIBILITY_VISIBLE;
		}

		/**
		 * Para obtener el nombre del campo el cual sera parte del perfil
		 *
		 * @return string
		 */
		public function getFieldName () {
			return $this->fieldName;
		}

		/**
		 * Para obtener el nombre del modulo el cual sera parte del perfil
		 *
		 * @return string
		 */
		public function getModuleName () {
			return $this->moduleName;
		}

		/**
		 * Para obtener el nombre del perfil a configurar
		 *
		 * @return string
		 */
		public function getProfileName () {
			return $this->profileName;
		}

		/**
		 * Para obtener el permiso de lectura unicamente para el campo
		 *
		 * @return integer
		 */
		public function getReadOnly () {
			if (!in_array ($this->readOnly, array (self::READ_ONLY, self::READ_WRITE))) {
				return self::READ_WRITE;
			} else {
				return $this->readOnly;
			}
		}

		/**
		 * Para obtener la visibilidad del campo
		 *
		 * @return integer
		 */
		public function getVisibility () {
			if (!in_array ($this->visibility, array (self::VISIBILITY_HIDDEN, self::VISIBILITY_VISIBLE))) {
				return self::VISIBILITY_VISIBLE;
			} else {
				return $this->visibility;
			}
		}

		/**
		 * Establece el nombre del campo a ser incluido en el perfil
		 *
		 * @param string $fieldName Nombre del campo a tomar en cuenta para el perfil
		 *
		 * @return FieldProfile
		 */
		public function setFieldName ($fieldName) {
			$this->fieldName = $fieldName;
			return $this;
		}

		/**
		 * Establece el nombre del modulo a ser incluido en el perfil
		 *
		 * @param string $moduleName Nombre del modulo a tomar en cuenta para el perfil
		 *
		 * @return FieldProfile
		 */
		public function setModuleName ($moduleName) {
			$this->moduleName = $moduleName;
			return $this;
		}

		/**
		 * Establece el nombre del perfil
		 *
		 * @param string $profileName Nombre del perfil a configurar
		 *
		 * @return FieldProfile
		 */
		public function setProfileName ($profileName) {
			$this->profileName = $profileName;
			return $this;
		}

		/**
		 * Establece el permiso de lectura unicamente para el campo
		 *
		 * @param integer $readOnly
		 *
		 * @return FieldProfile
		 */
		public function setReadOnly ($readOnly) {
			if (in_array ($readOnly, array (self::READ_ONLY, self::READ_WRITE))) {
				$this->readOnly = $readOnly;
			}
			return $this;
		}

		/**
		 * Establece la visibilidad del campo a ser tomado para el perfil
		 *
		 * @param integer $visibility
		 *
		 * @return FieldProfile
		 */
		public function setVisibility ($visibility) {
			if (in_array ($visibility, array (self::VISIBILITY_HIDDEN, self::VISIBILITY_VISIBLE))) {
				$this->visibility = intval ($visibility);
			}
			return $this;
		}

		/**
		 * Copia los valores del perfil configurado para el campo
		 *
		 * @param FieldProfile $profile
		 */
		public function copyValuesFrom ($profile) {
			if ((empty ($profile)) || (!($profile instanceof FieldProfile))) {
				return;
			}

			$this->fieldName   = $profile->getFieldName ();
			$this->moduleName  = $profile->getModuleName ();
			$this->profileName = $profile->getProfileName ();
			$this->readOnly    = $profile->getReadOnly ();
			$this->visibility  = $profile->getVisibility ();
		}

		/**
		 * Duplica los atributos del perfil configurado para el campo
		 *
		 * @param string $newProfileName
		 *
		 * @return FieldProfile
		 * @throws FieldProfileException
		 */
		public function duplicate ($newProfileName) {
			$this->validate ();

			$object = new self ();
			return $object->setFieldName ($this->fieldName)
				->setModuleName ($this->moduleName)
				->setProfileName ($newProfileName)
				->setReadOnly ($this->readOnly)
				->setVisibility ($this->visibility);
		}

		/**
		 * Compara si dos perfil de campo son iguales entre si
		 *
		 * @param FieldProfile $profile
		 *
		 * @return boolean
		 */
		public function isEqualTo ($profile) {
			if (
				(empty ($profile)) ||
				(!($profile instanceof FieldProfile)) ||
				($this->fieldName != $profile->getFieldName ()) ||
				($this->moduleName != $profile->getModuleName ()) ||
				($this->profileName != $profile->getProfileName ()) ||
				($this->readOnly != $profile->getReadOnly ()) ||
				($this->visibility != $profile->getVisibility ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Valida que los atributos configurados para el perfil del campo sean validos
		 *
		 * @throws FieldProfileException
		 */
		public function validate () {
			if (empty ($this->fieldName)) {
				throw new FieldProfileException (FieldProfileException::ERROR_FIELD_PROFILE_EMPTY_FIELD_NAME);
			} else if (empty ($this->moduleName)) {
				throw new FieldProfileException (FieldProfileException::ERROR_FIELD_PROFILE_EMPTY_MODULE_NAME);
			} else if (empty ($this->profileName)) {
				throw new FieldProfileException (FieldProfileException::ERROR_FIELD_PROFILE_EMPTY_PROFILE_NAME);
			}
		}

		/**
		 * Instanciación de la clase FielProfile. Se obtiene un objeto FieldProfile con los atributos de la clase
		 *
		 * @return FieldProfile
		 */
		public static function getInstance () {
			return new self ();
		}

	}
