<?php
	require_once ('include/platzilla/Exceptions/ProfileException.php');
	require_once ('include/platzilla/Objects/FieldProfile.php');
	require_once ('include/platzilla/Objects/ModuleProfile.php');
	require_once ('include/platzilla/Objects/ProfileInterface.php');
	require_once ('include/platzilla/Objects/ViewProfile.php');
	require_once ('include/platzilla/Utils/MiscellaneousUtils.php');

	/**
	 * Class Profile
	 *
	 * Esta clase define el objeto "Perfil" el cual hace referencia a la asignación de perfiles que determinan cuáles son las acciones que puede realizar o no un usuario, en la "Instancia".
	 * La clase está asociada a los objetos "Perfil Vista", "Perfil Modulo" y "Perfil Campo".
	 */
	class Profile implements ProfileInterface {
		/** @var integer */
		private $id;

		/** @var string */
		private $description;

		/** @var integer */
		private $editPermission;

		/** @var FieldProfile[] */
		private $fieldProfiles;

		/** @var string */
		private $mainApplicationCode;

		/** @var ModuleProfile[] */
		private $moduleProfiles;

		/** @var string */
		private $name;

		/** @var string[] */
		private $secondaryApplicationCodes;

		/** @var integer */
		private $viewPermission;

		/** @var ViewProfile[] */
		private $viewProfiles;

		/**
		 * Profile constructor.
		 */
		public function __construct () {
			$this->editPermission = self::PERMISSION_ALLOW;
			$this->viewPermission = self::PERMISSION_ALLOW;
		}

		/**
		 * Realiza copia de los perfiles de campos
		 *
		 * @param FieldProfile[] $sourceFieldProfiles
		 */
		private function copyFieldProfiles ($sourceFieldProfiles) {
			$fieldProfiles = array ();
			foreach ($sourceFieldProfiles as $sourceFieldProfile) {
				$found = false;
				foreach ($this->fieldProfiles as $targetFieldProfile) {
					if (
						($sourceFieldProfile->getProfileName () == $targetFieldProfile->getProfileName ()) &&
						($sourceFieldProfile->getModuleName () == $targetFieldProfile->getModuleName ()) &&
						($sourceFieldProfile->getFieldName () == $targetFieldProfile->getFieldName ()) &&
						(!$targetFieldProfile->isEqualTo ($sourceFieldProfile))
					) {
						$targetFieldProfile->copyValuesFrom ($sourceFieldProfile);
						$fieldProfiles [] = $targetFieldProfile;
						$found            = true;
						break;
					}
				}
				if (!$found) {
					$targetFieldProfile = $sourceFieldProfile->duplicate ($sourceFieldProfile->getProfileName ());
					$fieldProfiles []   = $targetFieldProfile;
				}
			}
			$this->fieldProfiles = $fieldProfiles;
		}

		/**
		 * Realiza copia de los perfiles de campos desde otros perfiles
		 *
		 * @param Profile $profile
		 */
		private function copyFieldProfilesFrom ($profile) {
			$sourceFieldProfiles = $profile->getFieldProfiles ();
			if ((empty ($sourceFieldProfiles)) && (empty ($this->fieldProfiles))) {
				return;
			}

			if (empty ($sourceFieldProfiles)) {
				$this->fieldProfiles = null;
			} else if (empty ($this->fieldProfiles)) {
				$fieldProfiles = array ();
				foreach ($sourceFieldProfiles as $sourceFieldProfile) {
					$fieldProfiles [] = $sourceFieldProfile->duplicate ($sourceFieldProfile->getProfileName ());
				}
				$this->fieldProfiles = $fieldProfiles;
			} else {
				$this->copyFieldProfiles ($sourceFieldProfiles);
			}
		}

		/**
		 * Realiza copia del perfil del modulo
		 *
		 * @param ModuleProfile[] $sourceModuleProfiles
		 *
		 */
		private function copyModuleProfiles ($sourceModuleProfiles) {
			$moduleProfiles = array ();
			foreach ($sourceModuleProfiles as $sourceModuleProfile) {
				$found = false;
				foreach ($this->moduleProfiles as $targetModuleProfile) {
					if (
						($sourceModuleProfile->getProfileName () == $targetModuleProfile->getProfileName ()) &&
						($sourceModuleProfile->getModuleName () == $targetModuleProfile->getModuleName ()) &&
						(!$targetModuleProfile->isEqualTo ($sourceModuleProfile))
					) {
						$targetModuleProfile->copyValuesFrom ($sourceModuleProfile);
						$moduleProfiles [] = $targetModuleProfile;
						$found             = true;
						break;
					}
				}
				if (!$found) {
					$targetModuleProfile = $sourceModuleProfile->duplicate ($this->name);
					$moduleProfiles []   = $targetModuleProfile;
				}
			}
			$this->moduleProfiles = $moduleProfiles;
		}

		/**
		 * Realiza copia del perfil del modulo desde otro perfil
		 *
		 * @param Profile $profile
		 */
		private function copyModuleProfilesFrom ($profile) {
			$sourceModuleProfiles = $profile->getModuleProfiles ();
			if ((empty ($sourceModuleProfiles)) && (empty ($this->moduleProfiles))) {
				return;
			}

			if (empty ($sourceModuleProfiles)) {
				$this->moduleProfiles = null;
			} else if (empty ($this->moduleProfiles)) {
				$moduleProfiles = array ();
				foreach ($sourceModuleProfiles as $sourceModuleProfile) {
					$moduleProfiles [] = $sourceModuleProfile->duplicate ($this->name);
				}
				$this->moduleProfiles = $moduleProfiles;
			} else {
				$this->copyModuleProfiles ($sourceModuleProfiles);
			}
		}

		/**
		 * Realiza copia de la vista del perfil
		 *
		 * @param ViewProfile[] $sourceViewProfiles
		 */
		private function copyViewProfiles ($sourceViewProfiles) {
			$viewProfiles = array ();
			foreach ($sourceViewProfiles as $sourceViewProfile) {
				$found = false;
				foreach ($this->viewProfiles as $targetViewProfile) {
					if (
						($sourceViewProfile->getProfileName () == $targetViewProfile->getProfileName ()) &&
						($sourceViewProfile->getModuleName () == $targetViewProfile->getModuleName ()) &&
						(!$targetViewProfile->isEqualTo ($sourceViewProfile))
					) {
						$targetViewProfile->copyValuesFrom ($sourceViewProfile);
						$viewProfiles [] = $targetViewProfile;
						$found           = true;
						break;
					}
				}
				if (!$found) {
					$targetViewProfile = $sourceViewProfile->duplicate ($sourceViewProfile->getProfileName ());
					$viewProfiles []   = $targetViewProfile;
				}
			}
			$this->viewProfiles = $viewProfiles;
		}

		/**
		 * Realiza copia de la vista del perfil desde otro perfil
		 *
		 * @param Profile $profile
		 */
		private function copyViewProfilesFrom ($profile) {
			$sourceViewProfiles = $profile->getViewProfiles ();
			if ((empty ($sourceViewProfiles)) && (empty ($this->viewProfiles))) {
				return;
			}

			if (empty ($sourceViewProfiles)) {
				$this->viewProfiles = null;
			} else if (empty ($this->viewProfiles)) {
				$viewProfiles = array ();
				foreach ($sourceViewProfiles as $sourceViewProfile) {
					$viewProfiles [] = $sourceViewProfile->duplicate ($sourceViewProfile->getProfileName ());
				}
				$this->viewProfiles = $viewProfiles;
			} else {
				$this->copyViewProfiles ($sourceViewProfiles);
			}
		}

		/**
		 * Duplica el perfil
		 *
		 * @param FieldProfile[]|ModuleProfile[]|ViewProfile[] $theseProfiles
		 * @param string $newProfileName
		 *
		 * @return array|null
		 */
		private function duplicateProfiles ($theseProfiles, $newProfileName) {
			if (empty ($theseProfiles)) {
				return null;
			}

			$profiles = array ();
			foreach ($theseProfiles as $thisProfile) {
				$profiles [] = $thisProfile->duplicate ($newProfileName);
			}
			return $profiles;
		}

		/**
		 * Para validar los perfiles del campo
		 *
		 * @throws ProfileException
		 */
		private function validateFieldProfiles () {
			if (empty ($this->fieldProfiles)) {
				return;
			}

			foreach ($this->fieldProfiles as $profile) {
				if (empty ($profile)) {
					throw new ProfileException (ProfileException::ERROR_PROFILE_EMPTY_FIELD_PROFILE);
				} else if (!($profile instanceof FieldProfile)) {
					throw new ProfileException (ProfileException::ERROR_PROFILE_INVALID_FIELD_PROFILE);
				} else {
					$profile->validate ();
				}
			}
		}

		/**
		 * Para validar las vistas de los perfiles
		 *
		 * @throws ProfileException
		 */
		private function validateViewProfiles () {
			if (empty ($this->viewProfiles)) {
				return;
			}

			foreach ($this->viewProfiles as $profile) {
				if (empty ($profile)) {
					throw new ProfileException (ProfileException::ERROR_PROFILE_EMPTY_VIEW_PROFILE);
				} else if (!($profile instanceof ViewProfile)) {
					throw new ProfileException (ProfileException::ERROR_PROFILE_INVALID_VIEW_PROFILE);
				} else {
					$profile->validate ();
				}
			}
		}

		/**
		 * Para validar el perfil del modulo
		 *
		 * @throws ProfileException
		 */
		private function validateModuleProfiles () {
			if (empty ($this->moduleProfiles)) {
				return;
			}

			foreach ($this->moduleProfiles as $profile) {
				if (empty ($profile)) {
					throw new ProfileException (ProfileException::ERROR_PROFILE_EMPTY_MODULE_PROFILE);
				} else if (!($profile instanceof ModuleProfile)) {
					throw new ProfileException (ProfileException::ERROR_PROFILE_INVALID_MODULE_PROFILE);
				} else {
					$profile->validate ();
				}
			}
		}

		/**
		 * Para obtener el ID del perfil
		 *
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * Para obtener la descripción del perfil
		 *
		 * @return string
		 */
		public function getDescription () {
			return $this->description;
		}

		/**
		 * Obtiene el permiso de edicion para el perfil
		 *
		 * @return integer
		 */
		public function getEditPermission () {
			return $this->editPermission;
		}

		/**
		 * Para obtener los perfiles de campo
		 *
		 * @return FieldProfile[]
		 */
		public function getFieldProfiles () {
			return $this->fieldProfiles;
		}

		/**
		 * Para obtener el codigo de la aplicacion principal asociada al perfil
		 *
		 * @return string
		 */
		public function getMainApplicationCode () {
			return $this->mainApplicationCode;
		}

		/** Para obtener los perfiles de modulo
		 *
		 * @return ModuleProfile[]
		 */
		public function getModuleProfiles () {
			return $this->moduleProfiles;
		}

		/**
		 * Para obtener el nombre para el perfil
		 *
		 * @return string
		 */
		public function getName () {
			return $this->name;
		}

		/**
		 * Para obtener el codigo para la aplicacion secundario asociada al perfil
		 *
		 * @return string[]
		 */
		public function getSecondaryApplicationCodes () {
			return $this->secondaryApplicationCodes;
		}

		/**
		 * Para obtener permiso de visualizacion para el perfil
		 *
		 * @return integer
		 */
		public function getViewPermission () {
			return $this->viewPermission;
		}

		/**
		 * Para obtener los perfiles de visualizacion
		 *
		 * @return ViewProfile[]
		 */
		public function getViewProfiles () {
			return $this->viewProfiles;
		}

		/**
		 * Establece el ID se le asociara al perfil
		 *
		 * @param integer $id
		 *
		 * @return $this
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}

		/**
		 * Establece la descripción del perfil
		 *
		 * @param string $description
		 *
		 * @return Profile
		 */
		public function setDescription ($description) {
			$this->description = $description;
			return $this;
		}

		/**
		 * Establece el permiso de edicion para el perfil
		 *
		 * @param integer $editPermission
		 *
		 * @return Profile
		 */
		public function setEditPermission ($editPermission) {
			if (in_array ($editPermission, array (self::PERMISSION_ALLOW, self::PERMISSION_DENY))) {
				$this->editPermission = $editPermission;
			}
			return $this;
		}

		/**
		 * Establece los perfiles de campo
		 *
		 * @param FieldProfile[] $fieldProfiles
		 *
		 * @return Profile
		 */
		public function setFieldProfiles ($fieldProfiles) {
			$this->fieldProfiles = $fieldProfiles;
			return $this;
		}

		/**
		 * Establece el codigo de la aplicacion principal asociada al perfil
		 *
		 * @param string $applicationCode
		 *
		 * @return Profile
		 */
		public function setMainApplicationCode ($applicationCode) {
			$this->mainApplicationCode = $applicationCode;
			return $this;
		}

		/**
		 * Establece los perfiles de modulo
		 *
		 * @param ModuleProfile[] $moduleProfiles
		 *
		 * @return Profile
		 */
		public function setModuleProfiles ($moduleProfiles) {
			$this->moduleProfiles = $moduleProfiles;
			return $this;
		}

		/**
		 * Establece el nombre para el perfil
		 *
		 * @param string $name
		 *
		 * @return Profile
		 */
		public function setName ($name) {
			$this->name = $name;
			return $this;
		}

		/**
		 * Establece el codigo para la aplicacion secundaria asociada al perfil
		 *
		 * @param string[] $applicationCodes
		 *
		 * @return Profile
		 */
		public function setSecondaryApplicationCodes ($applicationCodes) {
			if (($applicationCodes === null) || (((is_array ($applicationCodes)) && (!empty ($applicationCodes))))) {
				$this->secondaryApplicationCodes = $applicationCodes;
			}
			return $this;
		}

		/**
		 * Establece el permiso de visualizacion para el perfil
		 *
		 * @param integer $viewPermission
		 *
		 * @return Profile
		 */
		public function setViewPermission ($viewPermission) {
			if (in_array ($viewPermission, array (self::PERMISSION_ALLOW, self::PERMISSION_DENY))) {
				$this->viewPermission = $viewPermission;
			}
			return $this;
		}

		/**
		 * Establece los perfiles de visualizacion
		 *
		 * @param ViewProfile[] $viewProfiles
		 *
		 * @return Profile
		 */
		public function setViewProfiles ($viewProfiles) {
			$this->viewProfiles = $viewProfiles;
			return $this;
		}

		/**
		 * Realiza copia de los atributos establecidos para el perfil
		 *
		 * @param Profile $profile
		 */
		public function copyValuesFrom ($profile) {
			if ((empty ($profile)) || (!($profile instanceof Profile))) {
				return;
			}

			$this->description               = $profile->getDescription ();
			$this->editPermission            = $profile->getEditPermission ();
			$this->mainApplicationCode       = $profile->getMainApplicationCode ();
			$this->name                      = $profile->getName ();
			$this->secondaryApplicationCodes = $profile->getSecondaryApplicationCodes ();
			$this->viewPermission            = $profile->getViewPermission ();
			$this->copyFieldProfilesFrom ($profile);
			$this->copyModuleProfilesFrom ($profile);
			$this->copyViewProfilesFrom ($profile);
		}

		/**
		 * Duplica los atributos establecidos para el perfil
		 *
		 * @param integer $newProfileId
		 * @param string $newProfileName
		 * @param string $newProfileDescription
		 * @param string $newMainApplicationCode
		 *
		 * @return Profile
		 * @throws ProfileException
		 */
		public function duplicate ($newProfileId, $newProfileName, $newProfileDescription, $newMainApplicationCode) {
			$this->validate ();

			$object = new self ();
			return $object->setId ($newProfileId)
				->setDescription ($newProfileDescription)
				->setEditPermission ($this->editPermission)
				->setMainApplicationCode ($newMainApplicationCode)
				->setName ($newProfileName)
				->setViewPermission ($this->viewPermission)
				->setFieldProfiles ($this->duplicateProfiles ($this->fieldProfiles, $newProfileName))
				->setModuleProfiles ($this->duplicateProfiles ($this->moduleProfiles, $newProfileName))
				->setViewProfiles ($this->duplicateProfiles ($this->viewProfiles, $newProfileName))
				->setSecondaryApplicationCodes ($this->secondaryApplicationCodes);
		}

		/**
		 * Compara si un perfil es igual a otro
		 *
		 * @param Profile $profile
		 *
		 * @return boolean
		 */
		public function isEqualTo ($profile) {
			if (
				(empty ($profile)) ||
				(!($profile instanceof Profile)) ||
				($this->description != $profile->getDescription ()) ||
				($this->editPermission != $profile->getEditPermission ()) ||
				($this->mainApplicationCode != $profile->getMainApplicationCode ()) ||
				($this->name != $profile->getName ()) ||
				($this->viewPermission != $profile->getViewPermission ()) ||
				(!MiscellaneousUtils::areArrayValuesEqual ($this->secondaryApplicationCodes, $profile->getSecondaryApplicationCodes ())) ||
				(!MiscellaneousUtils::areObjectArraysEqual ($this->fieldProfiles, $profile->getFieldProfiles ())) ||
				(!MiscellaneousUtils::areObjectArraysEqual ($this->moduleProfiles, $profile->getModuleProfiles ())) ||
				(!MiscellaneousUtils::areObjectArraysEqual ($this->viewProfiles, $profile->getViewProfiles ()))
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Valida que los perfiles de campo, modulos y vistas esten correctamente definidos
		 *
		 * @throws ProfileException
		 */
		public function validate () {
			if (empty ($this->name)) {
				throw new ProfileException (ProfileException::ERROR_PROFILE_EMPTY_PROFILE_NAME);
			}
			$this->validateFieldProfiles ();
			$this->validateModuleProfiles ();
			$this->validateViewProfiles ();
		}

		/**
		 * Instanciación de la clase Profile. Se obtiene un objeto Profile con los valores de la clase
		 *
		 * @return Profile
		 */
		public static function getInstance () {
			return new self ();
		}

	}
