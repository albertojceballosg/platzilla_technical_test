<?php
	require_once ('include/platzilla/Exceptions/ModuleProfileException.php');
	require_once ('include/platzilla/Objects/ModuleProfileInterface.php');

	/**
	 * Class ModuleProfile
	 *
	 * En esta clase se define el objeto "Perfil Modulo" el cual hace referencia a los permisos que se pueden asignar a un módulo de una Aplicación.
	 **/
	class ModuleProfile implements ModuleProfileInterface {
		/** @var integer */
		private $accessPermission;

		/** @var integer */
		private $deletePermission;

		/** @var integer */
		private $editPermission;

		/** @var integer */
		private $exportPermission;

		/** @var integer */
		private $handleDuplicatesPermission;

		/** @var integer */
		private $importPermission;

		/** @var integer */
		private $listPermission;

		/** @var integer */
		private $mergePermission;

		/** @var string */
		private $moduleName;

		/** @var string */
		private $profileName;

		/** @var integer */
		private $readPermission;

		/** @var integer */
		private $savePermission;

		/**
		 * ModuleProfile constructor.
		 */
		public function __construct () {
			$this->accessPermission           = self::PERMISSION_ALLOW;
			$this->deletePermission           = self::PERMISSION_ALLOW;
			$this->editPermission             = self::PERMISSION_ALLOW;
			$this->exportPermission           = self::PERMISSION_ALLOW;
			$this->handleDuplicatesPermission = self::PERMISSION_ALLOW;
			$this->importPermission           = self::PERMISSION_ALLOW;
			$this->listPermission             = self::PERMISSION_ALLOW;
			$this->mergePermission            = self::PERMISSION_DENY;
			$this->readPermission             = self::PERMISSION_ALLOW;
			$this->savePermission             = self::PERMISSION_ALLOW;
		}

		/**
		 * Para obtener los permisos de acceso de modulos
		 *
		 * @return integer
		 */
		public function getAccessPermission () {
			return $this->accessPermission;
		}

		/**
		 * Para obtener los permisos para eliminar de modulos
		 *
		 * @return integer
		 */
		public function getDeletePermission () {
			return $this->deletePermission;
		}

		/**
		 * Para obtener los permisos de edicion de modulos
		 *
		 * @return integer
		 */
		public function getEditPermission () {
			return $this->editPermission;
		}

		/**
		 * Para obtener los permisos de exportar modulos
		 *
		 * @return integer
		 */
		public function getExportPermission () {
			return $this->exportPermission;
		}

		/**
		 * Obtener permisos para manejar duplicacion modulos
		 *
		 * @return integer
		 */
		public function getHandleDuplicatesPermission () {
			return $this->handleDuplicatesPermission;
		}

		/**
		 * Obtener permisos para importar modulos
		 *
		 * @return integer
		 */
		public function getImportPermission () {
			return $this->importPermission;
		}

		/**
		 * Para obtener permisos para listar modulos
		 *
		 * @return integer
		 */
		public function getListPermission () {
			return $this->listPermission;
		}

		/**
		 * Para obtener permisos de fusion de modulos
		 *
		 * @return integer
		 */
		public function getMergePermission () {
			return $this->mergePermission;
		}

		/**
		 * Para obtener el nombre del modulo
		 *
		 * @return string
		 */
		public function getModuleName () {
			return $this->moduleName;
		}

		/**
		 * Para obtener el nombre del perfil del modulo
		 *
		 * @return string
		 */
		public function getProfileName () {
			return $this->profileName;
		}

		/**
		 * Para obtener permisos de lectura para el modulo
		 *
		 * @return integer
		 */
		public function getReadPermission () {
			return $this->readPermission;
		}

		/**
		 * Para obtener permisos de guardado para el modulo
		 *
		 * @return integer
		 */
		public function getSavePermission () {
			return $this->savePermission;
		}

		/**
		 * Establecer permisos de acceso para el modulo
		 *
		 * @param integer $permission
		 *
		 * @return ModuleProfile
		 */
		public function setAccessPermission ($permission) {
			if (in_array ($permission, array (self::PERMISSION_ALLOW, self::PERMISSION_DENY))) {
				$this->accessPermission = $permission;
			}
			return $this;
		}

		/**
		 * Establecer permisos de borrado para el modulo
		 *
		 * @param integer $permission
		 *
		 * @return ModuleProfile
		 */
		public function setDeletePermission ($permission) {
			if (in_array ($permission, array (self::PERMISSION_ALLOW, self::PERMISSION_DENY))) {
				$this->deletePermission = $permission;
			}
			return $this;
		}

		/**
		 * Establecer permisos de edicion para el modulo
		 *
		 * @param integer $permission
		 *
		 * @return ModuleProfile
		 */
		public function setEditPermission ($permission) {
			if (in_array ($permission, array (self::PERMISSION_ALLOW, self::PERMISSION_DENY))) {
				$this->editPermission = $permission;
			}
			return $this;
		}

		/**
		 * Establecer permisos de exportacion para el modulo
		 *
		 * @param integer $permission
		 *
		 * @return ModuleProfile
		 */
		public function setExportPermission ($permission) {
			if (in_array ($permission, array (self::PERMISSION_ALLOW, self::PERMISSION_DENY))) {
				$this->exportPermission = $permission;
			}
			return $this;
		}

		/**
		 * Establecer permisos para manejar duplicacion modulos
		 *
		 * @param integer $permission
		 *
		 * @return ModuleProfile
		 */
		public function setHandleDuplicatesPermission ($permission) {
			if (in_array ($permission, array (self::PERMISSION_ALLOW, self::PERMISSION_DENY))) {
				$this->handleDuplicatesPermission = $permission;
			}
			return $this;
		}

		/**
		 * Establecer permisos para importar modulos
		 *
		 * @param integer $permission
		 *
		 * @return ModuleProfile
		 */
		public function setImportPermission ($permission) {
			if (in_array ($permission, array (self::PERMISSION_ALLOW, self::PERMISSION_DENY))) {
				$this->importPermission = $permission;
			}
			return $this;
		}

		/**
		 * Establecer permisos para listar modulos
		 *
		 * @param integer $permission
		 *
		 * @return ModuleProfile
		 */
		public function setListPermission ($permission) {
			if (in_array ($permission, array (self::PERMISSION_ALLOW, self::PERMISSION_DENY))) {
				$this->listPermission = $permission;
			}
			return $this;
		}

		/**
		 * Establecer permisos para fusionar modulos
		 *
		 * @param integer $permission
		 *
		 * @return ModuleProfile
		 */
		public function setMergePermission ($permission) {
			if (in_array ($permission, array (self::PERMISSION_ALLOW, self::PERMISSION_DENY))) {
				$this->mergePermission = $permission;
			}
			return $this;
		}

		/**
		 * Establecer el nombre del modulo
		 *
		 * @param string $moduleName
		 *
		 * @return ModuleProfile
		 */
		public function setModuleName ($moduleName) {
			$this->moduleName = $moduleName;
			return $this;
		}

		/**
		 * Establecer el nombre del perfil para el modulo
		 *
		 * @param string $profileName
		 *
		 * @return ModuleProfile
		 */
		public function setProfileName ($profileName) {
			$this->profileName = $profileName;
			return $this;
		}

		/**
		 * Establecer permiso de lectura para el modulo
		 *
		 * @param integer $permission
		 *
		 * @return ModuleProfile
		 */
		public function setReadPermission ($permission) {
			if (in_array ($permission, array (self::PERMISSION_ALLOW, self::PERMISSION_DENY))) {
				$this->readPermission = $permission;
			}
			return $this;
		}

		/**
		 * Establecer permisos de guardado para el modulo
		 *
		 * @param integer $permission
		 *
		 * @return ModuleProfile
		 */
		public function setSavePermission ($permission) {
			if (in_array ($permission, array (self::PERMISSION_ALLOW, self::PERMISSION_DENY))) {
				$this->savePermission = $permission;
			}
			return $this;
		}

		/**
		 * Realizar copia de los permisos se obtuvieron y establecieron para el modulo
		 *
		 * @param ModuleProfile $profile
		 */
		public function copyValuesFrom ($profile) {
			if ((empty ($profile)) || (!($profile instanceof ModuleProfile))) {
				return;
			}

			$this->accessPermission           = $profile->getAccessPermission ();
			$this->deletePermission           = $profile->getDeletePermission ();
			$this->editPermission             = $profile->getEditPermission ();
			$this->exportPermission           = $profile->getExportPermission ();
			$this->handleDuplicatesPermission = $profile->getHandleDuplicatesPermission ();
			$this->importPermission           = $profile->getImportPermission ();
			$this->listPermission             = $profile->getListPermission ();
			$this->mergePermission            = $profile->getMergePermission ();
			$this->moduleName                 = $profile->getModuleName ();
			$this->profileName                = $profile->getProfileName ();
			$this->readPermission             = $profile->getReadPermission ();
			$this->savePermission             = $profile->getSavePermission ();
		}

		/**
		 * Para realizar la accion de duplicar los permisos se obtueron y establecieron para el modulo
		 *
		 * @param string $newProfileName
		 *
		 * @return ModuleProfile
		 * @throws ModuleProfileException
		 */
		public function duplicate ($newProfileName) {
			$this->validate ();

			$object = new self ();
			return $object->setAccessPermission ($this->accessPermission)
				->setDeletePermission ($this->deletePermission)
				->setEditPermission ($this->editPermission)
				->setExportPermission ($this->exportPermission)
				->setHandleDuplicatesPermission ($this->handleDuplicatesPermission)
				->setImportPermission ($this->importPermission)
				->setListPermission ($this->listPermission)
				->setMergePermission ($this->mergePermission)
				->setModuleName ($this->moduleName)
				->setProfileName ($newProfileName)
				->setReadPermission ($this->readPermission)
				->setSavePermission ($this->savePermission);
		}

		/**
		 * Para realizar comparacion si es igual la permisologia de un modulo con otro
		 *
		 * @param ModuleProfile $profile
		 *
		 * @return boolean
		 */
		public function isEqualTo ($profile) {
			if (
				(empty ($profile)) ||
				(!($profile instanceof ModuleProfile)) ||
				($this->accessPermission != $profile->getAccessPermission ()) ||
				($this->deletePermission != $profile->getDeletePermission ()) ||
				($this->editPermission != $profile->getEditPermission ()) ||
				($this->exportPermission != $profile->getExportPermission ()) ||
				($this->handleDuplicatesPermission != $profile->getHandleDuplicatesPermission ()) ||
				($this->importPermission != $profile->getImportPermission ()) ||
				($this->listPermission != $profile->getListPermission ()) ||
				($this->mergePermission != $profile->getMergePermission ()) ||
				($this->moduleName != $profile->getModuleName ()) ||
				($this->profileName != $profile->getProfileName ()) ||
				($this->readPermission != $profile->getReadPermission ()) ||
				($this->savePermission != $profile->getSavePermission ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Valida si el perfil del modulo tiene asignado nombre y no esta vacio
		 *
		 * @throws ModuleProfileException
		 */
		public function validate () {
			if (empty ($this->moduleName)) {
				throw new ModuleProfileException (ModuleProfileException::ERROR_MODULE_PROFILE_EMPTY_MODULE_NAME);
			} else if (empty ($this->profileName)) {
				throw new ModuleProfileException (ModuleProfileException::ERROR_MODULE_PROFILE_EMPTY_PROFILE_NAME);
			}
		}

		/**
		 * Instanciación de la clase Moduleprofile. Se obtiene un objeto ModuleProfile con los atributos de la clase
		 *
		 * @return ModuleProfile
		 */
		public static function getInstance () {
			return new self ();
		}

	}
