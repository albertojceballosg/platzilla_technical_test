<?php
	require_once ('include/platzilla/Exceptions/ApplicationException.php');
	require_once ('include/platzilla/Objects/ApplicationInterface.php');
	require_once ('include/platzilla/Objects/Module.php');
	require_once ('include/platzilla/Objects/Profile.php');
	require_once ('include/platzilla/Utils/MiscellaneousUtils.php');

	/**
	 * Class Application
	 *
	 * En esta clase se define el objeto "Aplicacion" el cual hace referencia a las aplicaciones que forman parte de una "Instancia".
	 **/
	class Application implements ApplicationInterface {
		/** @var integer */
		private $id;

		/** @var integer */
		private $categoryId;

		/** @var string */
		private $code;

		/** @var string */
		private $description;

		/** @var Module[] */
		private $modules;

		/** @var string */
		private $name;

		/** @var float */
		private $price;

		/** @var Profile */
		private $profile;

		/** @var Profile[] */
		private $secondaryProfiles;

		/** @var string */
		private $status;

		/** @var string */
		private $url;

		/**
		 * PlatformManager Application
		 *
		 */
		public function __construct () {
			$this->price  = 0.0;
			$this->status = self::STATUS_ACTIVE;
		}

		/**
		 * Compara si los modulos asociados a una aplicacion son iguales
		 *
		 * @param Module[] $theseModules
		 * @param Module[] $thoseModules
		 *
		 * @return boolean
		 */
		private function areModulesEquals ($theseModules, $thoseModules) {
			if ((empty ($theseModules)) && (empty ($thoseModules))) {
				return true;
			} else if (
				(empty ($theseModules) !== empty ($thoseModules)) ||
				(!is_array ($thoseModules)) ||
				(count ($theseModules) != count ($thoseModules))
			) {
				return false;
			} else {
				foreach ($theseModules as $thisModule) {
					$equals = false;
					foreach ($thoseModules as $thatModule) {
						if (($thisModule->getName () == $thatModule->getName ()) && ($thisModule->isEqualTo ($thatModule))) {
							$equals = true;
							break;
						}
					}
					if (!$equals) {
						return false;
					}
				}
				return true;
			}
		}

		/**
		 * Para realizar copia de los modulos asociados a una aplicacion
		 *
		 * @param Module[] $sourceModules
		 */
		private function copyModules ($sourceModules) {
			$modules = array ();
			foreach ($sourceModules as $sourceModule) {
				$found = false;
				foreach ($this->modules as $targetModule) {
					if ($sourceModule->getName () != $targetModule->getName ()) {
						continue;
					} else if (!$targetModule->isEqualTo ($sourceModule)) {
						$targetModule->copyValuesFrom ($sourceModule);
					}
					$modules [] = $targetModule;
					$found      = true;
					break;
				}
				if (!$found) {
					$modules [] = Module::getInstance ($sourceModule->getIsEntityType (), $sourceModule->getEntityPrefix (), $sourceModule->getEntityInitialSequence ())
						->setEntityIdentifier ($sourceModule->getEntityIdentifier ())
						->setLabel ($sourceModule->getLabel ())
						->setMenuLabel ($sourceModule->getMenuLabel ())
						->setName ($sourceModule->getName ())
						->setPresence ($sourceModule->getPresence ())
						->setSequence ($sourceModule->getSequence ())
						->setShowInAdminConsole ($sourceModule->getShowInAdminConsole ())
						->setType ($sourceModule->getType ());
				}
			}
			$this->modules = $modules;
		}

		/**
		 * Para realizar copia de modulos desde una aplicacion a otra
		 *
		 * @param Application $application
		 */
		private function copyModulesFrom ($application) {
			$sourceModules = $application->getModules ();
			if ((empty ($sourceModules)) && (empty ($this->modules))) {
				return;
			}

			if (empty ($sourceModules)) {
				$this->modules = null;
			} else if (empty ($this->modules)) {
				$modules = array ();
				foreach ($sourceModules as $sourceModule) {
					$modules [] = Module::getInstance ($sourceModule->getIsEntityType (), $sourceModule->getEntityPrefix (), $sourceModule->getEntityInitialSequence ())
						->setEntityIdentifier ($sourceModule->getEntityIdentifier ())
						->setLabel ($sourceModule->getLabel ())
						->setMenuLabel ($sourceModule->getMenuLabel ())
						->setName ($sourceModule->getName ())
						->setPresence ($sourceModule->getPresence ())
						->setSequence ($sourceModule->getSequence ())
						->setShowInAdminConsole ($sourceModule->getShowInAdminConsole ())
						->setType ($sourceModule->getType ());
				}
				$this->modules = $modules;
			} else {
				$this->copyModules ($sourceModules);
			}
		}

		/**
		 * Para realizar copia de los perfiles secundarios asociados a una aplicacion
		 *
		 * @param Profile[] $sourceProfiles
		 */
		private function copySecondaryProfiles ($sourceProfiles) {
			$profiles = array ();
			foreach ($sourceProfiles as $sourceProfile) {
				$found = false;
				foreach ($this->secondaryProfiles as $targetProfile) {
					if ($sourceProfile->getName () != $targetProfile->getName ()) {
						continue;
					} else if (!$targetProfile->isEqualTo ($sourceProfile)) {
						$targetProfile->copyValuesFrom ($sourceProfile);
					}
					$profiles [] = $targetProfile;
					$found       = true;
					break;
				}
				if (!$found) {
					$profiles [] = $sourceProfile->duplicate (null, $sourceProfile->getName (), $sourceProfile->getDescription (), $sourceProfile->getMainApplicationCode ());
				}
			}
			$this->secondaryProfiles = $profiles;
		}

		/**
		 * Para realizar copia de perfiles secundarios desde otros perfiles
		 *
		 * @param Application $application
		 */
		private function copySecondaryProfilesFrom ($application) {
			$sourceProfiles = $application->getSecondaryProfiles ();
			if ((empty ($sourceProfiles)) && (empty ($this->secondaryProfiles))) {
				return;
			}

			if (empty ($sourceProfiles)) {
				$this->secondaryProfiles = null;
			} else if (empty ($this->secondaryProfiles)) {
				$profiles = array ();
				foreach ($sourceProfiles as $sourceProfile) {
					$profiles [] = $sourceProfile->duplicate (null, $sourceProfile->getName (), $sourceProfile->getDescription (), $sourceProfile->getMainApplicationCode ());
				}
				$this->secondaryProfiles = $profiles;
			} else {
				$this->copySecondaryProfiles ($sourceProfiles);
			}
		}

		/**
		 * Realiza el duplicado de los perfiles secundarios
		 *
		 * @param boolean $removeIds
		 *
		 * @return Profile[]|null
		 */
		private function duplicateSecondaryProfiles ($removeIds) {
			if (empty ($this->secondaryProfiles)) {
				return null;
			}
			$profiles = array ();
			foreach ($this->secondaryProfiles as $profile) {
				$profiles [] = $profile->duplicate (!$removeIds ? $profile->getId () : null, $profile->getName (), $profile->getDescription (), $profile->getMainApplicationCode ());
			}
			return $profiles;
		}

		/**
		 * Para obtener el ID de las aplicaciones posee actualmente la instancia
		 *
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * Para obtener el ID de la categoria a la cual pertenecen las aplicaciones asociados a la instancia
		 *
		 * @return string
		 */
		public function getCategoryId () {
			return $this->categoryId;
		}

		/**
		 * Para obtener el codigo asociado a la aplicacion
		 *
		 * @return string
		 */
		public function getCode () {
			return $this->code;
		}

		/**
		 * Para obtener la descripcion asociado a la aplicacion
		 *
		 * @return string
		 */
		public function getDescription () {
			return $this->description;
		}

		/**
		 * Para obtener el listado de los modulos disponibles
		 *
		 * @return Module[]
		 */
		public function getModules () {
			return $this->modules;
		}

		/**
		 * Para obtener el nombre de la aplicacion
		 *
		 * @return string
		 */
		public function getName () {
			return $this->name;
		}

		/**
		 * Para obtener el precio asociado a la aplicacion
		 *
		 * @return float
		 */
		public function getPrice () {
			return $this->price;
		}

		/**
		 * Para obtener el perfil asociado a la aplicacion
		 *
		 * @return Profile
		 */
		public function getProfile () {
			return $this->profile;
		}

		/**
		 * Para obtener el perfil asociado a la aplicacion
		 *
		 * @return Profile[]
		 */
		public function getSecondaryProfiles () {
			return $this->secondaryProfiles;
		}

		/**
		 * Para obtener el estatus de la instancia, siendo paga o vencida
		 *
		 * @return string
		 */
		public function getStatus () {
			return $this->status;
		}

		/**
		 * Para obtener el URL de la aplicacion
		 *
		 * @return string
		 */
		public function getUrl () {
			return $this->url;
		}

		/**
		 * Establece el ID de la aplicacion
		 *
		 * @param integer $id
		 *
		 * @return Application
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}

		/**
		 * Establece el ID de la categoria asociado a la aplicacion
		 *
		 * @param integer $categoryId
		 *
		 * @return Application
		 */
		public function setCategoryId ($categoryId) {
			$this->categoryId = $categoryId;
			return $this;
		}

		/**
		 * Establece el codigo se le asociara a la aplicacion
		 *
		 * @param string $code
		 *
		 * @return Application
		 */
		public function setCode ($code) {
			$this->code = $code;
			return $this;
		}

		/**
		 * Establece la descripcion se le asociara a la aplicacion
		 *
		 * @param string $description
		 *
		 * @return Application
		 */
		public function setDescription ($description) {
			$this->description = $description;
			return $this;
		}

		/**
		 * Establece los modulos que forman parte de la aplicacion
		 *
		 * @param Module[] $modules
		 *
		 * @return Application
		 */
		public function setModules ($modules) {
			$this->modules = $modules;
			return $this;
		}

		/**
		 * Establece el nombre se le asociara a la aplicacion
		 *
		 * @param string $name
		 *
		 * @return Application
		 */
		public function setName ($name) {
			$this->name = $name;
			return $this;
		}

		/**
		 * Establece el precio se le asignara a la aplicacion
		 *
		 * @param float $price
		 *
		 * @return Application
		 */
		public function setPrice ($price) {
			if ((is_numeric ($price)) && ($price >= 0)) {
				$this->price = $price;
			}
			return $this;
		}

		/**
		 * Establece el perfil o los perfiles se le asociaran a la aplicacion
		 *
		 * @param Profile $profile
		 *
		 * @return Application
		 */
		public function setProfile ($profile) {
			if ((!empty ($profile)) && ($profile instanceof Profile)) {
				$this->profile = $profile;
			}
			return $this;
		}

		/**
		 * Establece los perfiles secundarios se le asociara a la aplicacion
		 *
		 * @param Profile[] $secondaryProfiles
		 *
		 * @return Application
		 */
		public function setSecondaryProfiles ($secondaryProfiles) {
			if ((!empty ($secondaryProfiles)) && (is_array ($secondaryProfiles))) {
				$this->secondaryProfiles = $secondaryProfiles;
			}
			return $this;
		}

		/**
		 * Establece el estatus tendra la aplicacion
		 *
		 * @param string $status
		 *
		 * @return Application
		 */
		public function setStatus ($status) {
			if (in_array ($status, array (self::STATUS_ACTIVE, self::STATUS_INACTIVE))) {
				$this->status = $status;
			}
			return $this;
		}

		/**
		 * Establece el URL se le asignara a la aplicacion
		 *
		 * @param string $url
		 *
		 * @return Application
		 */
		public function setUrl ($url) {
			$this->url = $url;
			return $this;
		}

		/**
		 * Copia los atributos de la aplicacion desde una aplicacion a otra
		 *
		 * @param Application $application
		 */
		public function copyValuesFrom ($application) {
			if ((empty ($application)) || (!($application instanceof Application))) {
				return;
			}

			$this->code        = $application->getCode ();
			$this->description = $application->getDescription ();
			$this->name        = $application->getName ();
			$this->price       = $application->getPrice ();
			$this->status      = $application->getStatus ();
			$this->url         = $application->getUrl ();
			$this->copyModulesFrom ($application);
			if (!empty ($this->profile)) {
				$this->profile->copyValuesFrom ($application->getProfile ());
			}
			$this->copySecondaryProfilesFrom ($application);
		}

		/**
		 * Hace el duplicado de una aplicacion desde una que se seleccione para realizarlo
		 *
		 * @param integer $newApplicationId
		 * @param string $newApplicationName
		 * @param string $newApplicationDescription
		 *
		 * @return Application
		 * @throws ApplicationException
		 */
		public function duplicate ($newApplicationId, $newApplicationName, $newApplicationDescription) {
			$this->validate ();

			$object = new self ();
			return $object->setId ($newApplicationId)
				->setCategoryId ($this->categoryId)
				->setCode ($this->code)
				->setDescription ($newApplicationDescription)
				->setModules ($this->modules)
				->setName ($newApplicationName)
				->setPrice ($this->price)
				->setProfile (!empty ($this->profile) ? $this->profile->duplicate (null, $newApplicationName, $newApplicationDescription, $this->code) : null)
				->setSecondaryProfiles ($this->duplicateSecondaryProfiles (true))
				->setStatus ($this->status)
				->setUrl ($this->url);
		}

		/**
		 * Compara si dos aplicaciones son iguales
		 *
		 * @param Application $application
		 * @param boolean $deepCheck
		 *
		 * @return boolean
		 */
		public function isEqualTo ($application, $deepCheck = true) {
			if (
				(empty ($application)) ||
				(!($application instanceof Application)) ||
				($this->code != $application->getCode ()) ||
				($this->description != $application->getDescription ()) ||
				($this->name != $application->getName ()) ||
				($this->price != $application->getPrice ()) ||
				($this->status != $application->getStatus ()) ||
				($this->url != $application->getUrl ()) ||
				(($deepCheck) && ((!$this->areModulesEquals ($this->modules, $application->getModules ())) || (!MiscellaneousUtils::areObjectArraysEqual ($this->secondaryProfiles, $application->getSecondaryProfiles ()))))
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Valida que la aplicacion poseea todos los atributos por la cual se define
		 *
		 * @throws ApplicationException
		 */
		public function validate () {
			if (!isset ($this->categoryId)) {
				throw new ApplicationException (ApplicationException::ERROR_APPLICATION_EMPTY_CATEGORY_ID);
			} else if (empty ($this->code)) {
				throw new ApplicationException (ApplicationException::ERROR_APPLICATION_EMPTY_CODE);
			} else if (empty ($this->name)) {
				throw new ApplicationException (ApplicationException::ERROR_APPLICATION_EMPTY_NAME);
			} else if (empty ($this->modules)) {
				throw new ApplicationException (ApplicationException::ERROR_APPLICATION_EMPTY_MODULES);
			} else if (empty ($this->url)) {
				throw new ApplicationException (ApplicationException::ERROR_APPLICATION_EMPTY_URL);
			}

			foreach ($this->modules as $module) {
				if ((empty ($module)) || (!($module instanceof Module))) {
					throw new ApplicationException (ApplicationException::ERROR_APPLICATION_INVALID_MODULE);
				}
			}
		}

		/**
		 * Instanciacion de la clase Application. Se obtiene un objeto Application con los atributos de la clase
		 *
		 * @return Application
		 */
		public static function getInstance () {
			return new self ();
		}

	}
