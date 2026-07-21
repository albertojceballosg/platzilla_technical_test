<?php
	require_once ('include/platzilla/Exceptions/ApplicationException.php');
	require_once ('include/platzilla/Objects/ApplicationInterface.php');

	class ApplicationObject implements ApplicationInterface {
		/** @var integer */
		private $id;

		/** @var integer */
		private $categoryId;

		/** @var string */
		private $code;

		/** @var string */
		private $description;

		/** @var array */
		private $modules;

		/** @var string */
		private $name;

		/** @var float */
		private $price;

		/** @var Profile */
		private $profile;

		/** @var string */
		private $status;

		/** @var string */
		private $url;

		public function __construct () {
			$this->price  = 0.0;
			$this->status = self::STATUS_ACTIVE;
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
		 * @return array
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
		 * @return ApplicationObject
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
		 * @return ApplicationObject
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
		 * @return ApplicationObject
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
		 * @return ApplicationObject
		 */
		public function setDescription ($description) {
			$this->description = $description;
			return $this;
		}

		/**
		 * Establece los modulos que forman parte de la aplicacion
		 *
		 * @param array $modules
		 *
		 * @return ApplicationObject
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
		 * @return ApplicationObject
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
		 * @return ApplicationObject
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
		 * @return ApplicationObject
		 */
		public function setProfile ($profile) {
			if ((!empty ($profile)) && ($profile instanceof Profile)) {
				$this->profile = $profile;
			}
			return $this;
		}

		/**
		 * Establece el estatus tendra la aplicacion
		 *
		 * @param string $status
		 *
		 * @return ApplicationObject
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
		 * @return ApplicationObject
		 */
		public function setUrl ($url) {
			$this->url = $url;
			return $this;
		}

		/**
		 * Copia los atributos de la aplicacion desde una aplicacion a otra
		 *
		 * @param ApplicationObject $application
		 */
		public function copyValuesFrom ($application) {
			if ((empty ($application)) || (!($application instanceof ApplicationObject))) {
				return;
			}

			$this->code        = $application->getCode ();
			$this->description = $application->getDescription ();
			$this->name        = $application->getName ();
			$this->price       = $application->getPrice ();
			$this->status      = $application->getStatus ();
			$this->url         = $application->getUrl ();
			$this->profile     = $application->getProfile();
		}

		/**
		 * Hace el duplicado de una aplicacion desde una que se seleccione para realizarlo
		 *
		 * @param integer $newApplicationId
		 * @param string $newApplicationName
		 * @param string $newApplicationDescription
		 *
		 * @return ApplicationObject
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
				->setProfile ($this->getProfile())
				->setStatus ($this->status)
				->setUrl ($this->url);
		}

		/**
		 * Compara si dos aplicaciones son iguales
		 *
		 * @param ApplicationObject $application
		 *
		 * @return boolean
		 */
		public function isEqualTo ($application) {
			if (
				(empty ($application)) ||
				(!($application instanceof ApplicationObject)) ||
				($this->code != $application->getCode ()) ||
				($this->description != $application->getDescription ()) ||
				($this->name != $application->getName ()) ||
				($this->price != $application->getPrice ()) ||
				($this->status != $application->getStatus ()) ||
				($this->url != $application->getUrl ()) ||
				($this->profile != $application->getProfile())
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
		}

		/**
		 * Instanciacion de la clase Application. Se obtiene un objeto Application con los atributos de la clase
		 *
		 * @return ApplicationObject
		 */
		public static function getInstance () {
			return new self ();
		}

	}
